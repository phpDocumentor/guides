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
 * Accepted `guides.xml` attribute:
 *
 * ```xml
 * <extension
 *     class="phpDocumentor\Guides\Pages\DependencyInjection\PagesExtension"
 *     source-directory="pages/"
 * />
 * ```
 *
 * The `source-directory` value is the path (relative to the documentation
 * source root) that contains the standalone page source files. Defaults to
 * `"pages"`.
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('pages');
        $rootNode    = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->children()
                ->scalarNode('source_directory')
                    ->defaultValue('pages')
                    ->info('Path relative to the documentation source root that contains the page source files.')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
