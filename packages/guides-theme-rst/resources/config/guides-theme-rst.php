<?php

declare(strict_types=1);

use phpDocumentor\Guides\RstTheme\Renderer\RstRenderer;
use phpDocumentor\Guides\RstTheme\Twig\RstExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        /*
        ->load(
            'phpDocumentor\\Guides\RstTheme\\NodeRenderers\\Rst\\',
            '../../src/RstTheme/NodeRenderers/Rst',
        )
        ->tag('phpdoc.guides.noderenderer.rst')
        */

        ->set(RstRenderer::class)
        ->tag(
            'phpdoc.renderer.typerenderer',
            [
                'noderender_tag' => 'phpdoc.guides.noderenderer.rst',
                'format' => 'rst',
            ],
        )

        ->set(RstExtension::class)
        ->arg('$nodeRenderer', service('phpdoc.guides.output_node_renderer'))
        ->tag('twig.extension')
        ->autowire();
};
