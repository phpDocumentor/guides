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

use phpDocumentor\Guides\Pages\Collection;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function array_is_list;
use function dirname;
use function is_array;
use function ltrim;

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

        // Normalize collections: default overview_title to source_directory when
        // empty, then wrap each entry in a Collection value object.
        $collections = [];
        foreach ($config['collections'] as $collection) {
            if ($collection['overview_title'] === '') {
                $collection['overview_title'] = $collection['source_directory'];
            }

            $collections[] = Collection::fromArray($collection);
        }

        $container->setParameter('phpdoc.guides.pages.collections', $collections);

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
        $sourceDirectory = 'pages';
        $collectionSourceDirs = [];
        foreach ($container->getExtensionConfig($this->getAlias()) as $config) {
            if (isset($config['source_directory']) && $config['source_directory'] !== '') {
                $sourceDirectory = $config['source_directory'];
            }

            if (!isset($config['collection'])) {
                continue;
            }

            $collections = is_array($config['collection']) && array_is_list($config['collection'])
                ? $config['collection']
                : [$config['collection']];

            foreach ($collections as $collection) {
                $dir = $collection['source_directory'] ?? $collection['source-directory'] ?? null;
                if ($dir === null || $dir === '') {
                    continue;
                }

                $collectionSourceDirs[] = ltrim($dir, '/');
            }
        }

        $excludePaths = [$sourceDirectory . '/*'];
        foreach ($collectionSourceDirs as $dir) {
            $excludePaths[] = $dir . '/*';
        }

        $container->prependExtensionConfig('guides', [
            'base_template_paths' => [
                dirname(__DIR__, 3) . '/resources/template/page',
            ],

            'exclude' => ['paths' => $excludePaths],
        ]);
    }
}
