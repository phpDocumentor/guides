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

namespace phpDocumentor\Guides\RestructuredText\DependencyInjection;

use phpDocumentor\Guides\RestructuredText\DependencyInjection\Compiler\TextRolePass;
use phpDocumentor\Guides\RestructuredText\Nodes\ConfvalNode;
use phpDocumentor\Guides\RestructuredText\Nodes\OptionNode;
use phpDocumentor\Guides\RestructuredText\Nodes\VersionChangeNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function assert;
use function dirname;
use function phpDocumentor\Guides\DependencyInjection\template;

final class ReStructuredTextExtension extends Extension implements
    PrependExtensionInterface,
    CompilerPassInterface,
    ConfigurationInterface
{
    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 3) . '/resources/config'),
        );

        $normalizedLanguageLabels = [];
        foreach ($config['code_language_labels'] ?? [] as $item) {
            $normalizedLanguageLabels[$item['language']] = $item['label'];
        }

        $container->setParameter('phpdoc.rst.code_language_labels', $normalizedLanguageLabels);
        $loader->load('guides-restructured-text.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig(
            'guides',
            [
                'base_template_paths' => [
                    dirname(__DIR__, 3) . '/resources/template/html',
                    dirname(__DIR__, 3) . '/resources/template/latex',
                ],
                'templates' => [
                    template(ConfvalNode::class, 'body/directive/confval.html.twig'),
                    template(VersionChangeNode::class, 'body/version-change.html.twig'),
                    template(ConfvalNode::class, 'body/directive/confval.tex.twig', 'tex'),
                    template(OptionNode::class, 'body/directive/option.html.twig'),

                ],
            ],
        );
    }

    public function process(ContainerBuilder $container): void
    {
        (new TextRolePass())->process($container);
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('rst');
        $rootNode = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->fixXmlConfig('code_language_label', 'code_language_labels')
            ->children()
                ->arrayNode('code_language_labels')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('language')
                                ->isRequired()
                            ->end()
                            ->scalarNode('label')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /** @param mixed[] $config */
    public function getConfiguration(array $config, ContainerBuilder $container): static
    {
        return $this;
    }
}
