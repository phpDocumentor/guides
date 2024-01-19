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

namespace phpDocumentor\Guides\Graphs\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use function assert;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('Graphs');
        $rootNode = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode->children()
            ->scalarNode('renderer')
            ->defaultValue('plantuml-server')
            ->info('Render engine to use for generating graphs')
            ->end()
            ->scalarNode('plantuml_server')
            ->defaultValue('https://www.plantuml.com/plantuml')
            ->info('URL of the PlantUML server to use')
            ->end()
            ->scalarNode('plantuml_binary')
            ->defaultValue('plantuml')
            ->info('Path to your local PlantUML binary')
            ->end();

        return $treeBuilder;
    }
}
