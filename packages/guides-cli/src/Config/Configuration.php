<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use function assert;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('guides');
        $rootNode = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->fixXmlConfig('extension')
            ->children()
                ->arrayNode('extensions')
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(static fn ($n) => ['class' => $n])
                        ->end()
                        ->children()
                            ->scalarNode('class')
                                ->isRequired()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
