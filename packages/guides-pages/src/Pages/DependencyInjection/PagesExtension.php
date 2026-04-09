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

namespace phpDocumentor\Guides\Pages\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function array_keys;
use function array_map;
use function array_values;
use function dirname;

/**
 * Symfony DI extension for the guides-pages package.
 *
 * Registers the `page` output format renderer, its Twig templates, and the
 * {@see \phpDocumentor\Guides\Pages\EventListener\RunPagesListener} that drives
 * the standalone-pages sub-pipeline.
 *
 * Accepted `guides.xml` configuration:
 *
 * ```xml
 * <extension
 *     class="phpDocumentor\Guides\Pages\DependencyInjection\PagesExtension"
 *     source-directory="pages/"
 * />
 * ```
 */
final class PagesExtension extends Extension implements PrependExtensionInterface
{
    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'phpdoc.guides.pages.source_directory',
            $config['source_directory'],
        );

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 3) . '/resources/config'),
        );

        $loader->load('guides-pages.php');
    }

    /** @param mixed[] $config */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    public function prepend(ContainerBuilder $container): void
    {
        $templateMap = require dirname(__DIR__, 3) . '/resources/template/page/template.php';

        $sourceDirectory = 'pages';
        foreach ($container->getExtensionConfig($this->getAlias()) as $config) {
            if (!isset($config['source_directory']) || $config['source_directory'] === '') {
                continue;
            }

            $sourceDirectory = $config['source_directory'];
        }

        $container->prependExtensionConfig('guides', [
            'templates' => array_map(
                static fn (string $node, string $file): array => [
                    'node'   => $node,
                    'file'   => $file,
                    'format' => 'page',
                ],
                array_keys($templateMap),
                array_values($templateMap),
            ),
            'base_template_paths' => [
                dirname(__DIR__, 3) . '/resources/template/page',
            ],

            'exclude' => [
                'paths' => [$sourceDirectory . '/*'],
            ],
        ]);
    }
}
