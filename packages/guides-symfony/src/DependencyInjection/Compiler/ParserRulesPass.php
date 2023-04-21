<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ParserRulesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $body = $container->findDefinition('phpdoc.guides.parser.rst.body_elements');
        $structual = $container->findDefinition('phpdoc.guides.parser.rst.structural_elements');

        foreach ($container->findTaggedServiceIds('phpdoc.guides.parser.rst.structural_element') as $id => $tags) {
            $structual->addMethodCall('push', [new Reference($id)]);
        }

        foreach ($container->findTaggedServiceIds('phpdoc.guides.parser.rst.body_element') as $id => $tags) {
            $body->addMethodCall('push', [new Reference($id)]);
            //TODO: remove this call to $structual, body elements should not be part of it once subparser is removed
            $structual->addMethodCall('push', [new Reference($id)]);
        }
    }
}
