<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\DependencyInjection;

use phpDocumentor\Guides\DependencyInjection\Compiler\NodeRendererPass;
use phpDocumentor\Guides\DependencyInjection\Compiler\ParserRulesPass;
use phpDocumentor\Guides\DependencyInjection\Compiler\RendererPass;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Twig\Theme\ThemeConfig;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function assert;
use function dirname;
use function is_array;
use function is_int;
use function is_string;
use function pathinfo;
use function var_export;

final class GuidesExtension extends Extension implements CompilerPassInterface, ConfigurationInterface, PrependExtensionInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('guides');
        $rootNode = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->fixXmlConfig('template')
            ->fixXmlConfig('inventory', 'inventories')
            ->children()
                ->arrayNode('project')
                    ->children()
                        ->scalarNode('title')->end()
                        ->scalarNode('version')
                            ->beforeNormalization()
                            ->always(
                                // We need to revert the phpize call in XmlUtils. Version is always a string!
                                static function ($value) {
                                    if (!is_int($value) && !is_string($value)) {
                                        return var_export($value, true);
                                    }

                                    return $value;
                                },
                            )
                            ->end()
                        ->end()
                        ->scalarNode('release')->end()
                        ->scalarNode('copyright')->end()
                    ->end()
                ->end()
                ->arrayNode('inventories')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('id')
                                ->isRequired()
                            ->end()
                                ->scalarNode('url')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('theme')->end()
                ->scalarNode('input')->end()
                ->scalarNode('input_file')->end()
                ->scalarNode('output')->end()
                ->scalarNode('input_format')->end()
                ->arrayNode('output_format')
                    ->defaultValue(['html', 'interlink'])
                    ->beforeNormalization()
                    ->ifString()
                    ->then(static function ($value) {
                        return [$value];
                    })
                    ->end()
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('ignored_domain')
                    ->defaultValue([])
                    ->beforeNormalization()
                    ->ifString()
                    ->then(static function ($value) {
                        return [$value];
                    })
                    ->end()
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('log_path')->end()
                ->scalarNode('fail_on_log')->end()
                ->scalarNode('fail_on_error')->end()
                ->scalarNode('show_progress')->end()
                ->scalarNode('links_are_relative')->end()
                ->scalarNode('max_menu_depth')->end()
                ->arrayNode('base_template_paths')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('templates')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('node')
                                ->isRequired()
                            ->end()
                            ->scalarNode('file')
                                ->isRequired()
                            ->end()
                            ->scalarNode('format')
                                ->defaultValue('html')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('default_code_language')->defaultValue('')->end()
                ->arrayNode('themes')
                    ->defaultValue([])
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifTrue(static fn ($v) => !is_array($v) || !isset($v['templates']))
                            ->then(static fn (string $v) => ['templates' => (array) $v])
                        ->end()
                        ->children()
                            ->scalarNode('extends')->end()
                            ->arrayNode('templates')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 2) . '/resources/config'),
        );

        $loader->load('command_bus.php');
        $loader->load('guides.php');

        $projectSettings = new ProjectSettings();
        if (isset($config['project'])) {
            if (isset($config['project']['version'])) {
                $projectSettings->setVersion((string) $config['project']['version']);
            }

            if (isset($config['project']['title'])) {
                $projectSettings->setTitle((string) $config['project']['title']);
            }

            if (isset($config['project']['release'])) {
                $projectSettings->setRelease((string) $config['project']['release']);
            }

            if (isset($config['project']['copyright'])) {
                $projectSettings->setCopyright((string) $config['project']['copyright']);
            }
        }

        if (isset($config['inventories'])) {
            $projectSettings->setInventories($config['inventories']);
        }

        if (isset($config['theme'])) {
            $projectSettings->setTheme((string) $config['theme']);
        }

        if (isset($config['input'])) {
            $projectSettings->setInput((string) $config['input']);
        }

        if (isset($config['input_file']) && $config['input_file'] !== '') {
            $inputFile = (string) $config['input_file'];
            $pathInfo = pathinfo($inputFile);
            $projectSettings->setInputFile($pathInfo['filename']);
            if (!empty($pathInfo['extension'])) {
                $projectSettings->setInputFormat($pathInfo['extension']);
            }
        }

        if (isset($config['output'])) {
            $projectSettings->setOutput((string) $config['output']);
        }

        if (isset($config['input_format'])) {
            $projectSettings->setInputFormat((string) $config['input_format']);
        }

        if (isset($config['output_format']) && is_array($config['output_format'])) {
            $projectSettings->setOutputFormats($config['output_format']);
        }

        if (isset($config['ignored_domain']) && is_array($config['ignored_domain'])) {
            $projectSettings->setIgnoredDomains($config['ignored_domain']);
        }

        if (isset($config['links_are_relative'])) {
            $projectSettings->setLinksRelative((bool) $config['links_are_relative']);
        }

        if (isset($config['show_progress'])) {
            $projectSettings->setShowProgressBar((bool) $config['show_progress']);
        }

        if (isset($config['fail_on_error'])) {
            $projectSettings->setFailOnError(LogLevel::ERROR);
        }

        if (isset($config['fail_on_log'])) {
            $projectSettings->setFailOnError(LogLevel::WARNING);
        }

        if (isset($config['max_menu_depth'])) {
            $projectSettings->setMaxMenuDepth((int) $config['max_menu_depth']);
        }

        if (isset($config['default_code_language'])) {
            $projectSettings->setDefaultCodeLanguage((string) $config['default_code_language']);
        }

        $container->getDefinition(SettingsManager::class)
            ->addMethodCall('setProjectSettings', [$projectSettings]);

        $config['base_template_paths'][] = dirname(__DIR__, 2) . '/resources/template/html';
        $config['base_template_paths'][] = dirname(__DIR__, 2) . '/resources/template/tex';
        $container->setParameter('phpdoc.guides.base_template_paths', $config['base_template_paths']);
        $container->setParameter('phpdoc.guides.node_templates', $config['templates']);
        $container->setParameter('phpdoc.guides.inventories', $config['inventories']);

        foreach ($config['themes'] as $themeName => $themeConfig) {
            $container->getDefinition(ThemeManager::class)
                ->addMethodCall('registerTheme', [new ThemeConfig($themeName, $themeConfig['templates'], $themeConfig['extends'] ?? null)]);
        }
    }

    public function process(ContainerBuilder $container): void
    {
        (new ParserRulesPass())->process($container);
        (new NodeRendererPass())->process($container);
        (new RendererPass())->process($container);
    }

    /** @param mixed[] $config */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return $this;
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig(
            'guides',
            [
                'templates' => array_merge(
                    templateArray(
                        require dirname(__DIR__, 2) . '/resources/template/html/template.php',
                        'html',
                    ),
                    templateArray(
                        require dirname(__DIR__, 2) . '/resources/template/tex/template.php',
                        'tex',
                    ),
                ),
            ],
        );
    }
}

/**
 * Helper function to configure multiple templates.
 *
 * This function is used to configure the templates in the configuration file.
 *
 * @param array<class-string<Node>, string> $input
 *
 * @return array<array-key, array{node: class-string<Node>, file: string, format: string}>
 */
function templateArray(array $input, string $format = 'html'): array
{
    return array_map(
        static fn ($node, $template) => template($node, $template, $format),
        array_keys($input),
        array_values($input),
    );
}

/**
 * Helper function to configure templates.
 *
 * This function is used to configure the templates in the configuration file.
 *
 * @param class-string<Node> $node
 *
 * @return array{node: class-string<Node>, file: string, format: string}
 */
function template(string $node, string $template, string $format = 'html'): array
{
    return [
        'node' => $node,
        'file' => $template,
        'format' => $format,
    ];
}
