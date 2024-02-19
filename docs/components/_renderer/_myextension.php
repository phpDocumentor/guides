<?php

use MyVendor\MyExtension\Renderer\PlaintextRenderer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()->autowire()
        // ...
        ->set(PlaintextRenderer::class)
        ->tag(
            'phpdoc.renderer.typerenderer',
            [
                'noderender_tag' => 'phpdoc.guides.noderenderer.txt',
                'format' => 'txt',
            ],
        );
};