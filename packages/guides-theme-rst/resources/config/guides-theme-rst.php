<?php

declare(strict_types=1);

use phpDocumentor\Guides\RstTheme\Renderer\RstRenderer;
use phpDocumentor\Guides\RstTheme\Twig\RstExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        /*
        ->load(
            'phpDocumentor\\Guides\RstTheme\\NodeRenderers\\Rst\\',
            '%vendor_dir%/phpdocumentor/guides-rst-theme/src/RstTheme/NodeRenderers/Rst',
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
        ->tag('twig.extension')
        ->autowire();
};
