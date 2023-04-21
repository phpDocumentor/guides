<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as SymfonyExtension;

abstract class Extension extends SymfonyExtension implements ConfigurationInterface, CompilerPassInterface
{
    protected function configure(ArrayNodeDefinition $root): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return $this;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($this->getAlias());

        $this->configure($treeBuilder->getRootNode());

        return $treeBuilder;
    }
}
