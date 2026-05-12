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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use function assert;

/**
 * Symfony Config definition for the guides-pages extension.
 *
 * Accepted `guides.xml` configuration:
 *
 * ```xml
 * <extension
 *     class="phpDocumentor\Guides\Pages\DependencyInjection\PagesExtension"
 *     source-directory="pages">
 *     <collection
 *         source-directory="news"
 *         overview-path="news/index"
 *         overview-title="News"
 *         overview-template="structure/content-type-overview.html.twig"
 *         item-template="structure/content-type-item.html.twig"
 *     />
 *     <collection source-directory="blog" overview-path="blog/index" overview-title="Blog" />
 * </extension>
 * ```
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('pages');
        $rootNode    = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->fixXmlConfig('collection')
            ->children()
                ->scalarNode('source_directory')
                    ->defaultValue('pages')
                    ->info('Path relative to the documentation source root that contains the page source files.')
                ->end()
                ->arrayNode('collections')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('source_directory')
                                ->isRequired()
                                ->info('Source directory for content-type items, relative to the docs source root.')
                            ->end()
                            ->scalarNode('overview_path')
                                ->isRequired()
                                ->info('Output path for the generated overview page, without the .html extension.')
                            ->end()
                            ->scalarNode('overview_title')
                                ->defaultValue('')
                                ->info('Title for the overview page. Defaults to the source_directory value.')
                            ->end()
                            ->scalarNode('overview_template')
                                ->defaultValue('structure/content-type-overview.html.twig')
                                ->info('Twig template used to render the collection overview page.')
                            ->end()
                            ->scalarNode('item_template')
                                ->defaultValue('structure/content-type-item.html.twig')
                                ->info('Default Twig template for individual items. Can be overridden per item via :page-template:.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
