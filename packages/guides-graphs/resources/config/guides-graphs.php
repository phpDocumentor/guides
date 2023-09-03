<?php

declare(strict_types=1);

use phpDocumentor\Guides\Graphs\Directives\UmlDirective;
use phpDocumentor\Guides\Graphs\Renderer\PlantumlRenderer;
use phpDocumentor\Guides\Graphs\Twig\UmlExtension;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->instanceof(BaseDirective::class)
        ->tag('phpdoc.guides.directive')
        ->set(PlantumlRenderer::class)
        ->args(
            ['$plantUmlBinaryPath' => 'plantuml'],
        )
        ->set(UmlExtension::class)
        ->args(
            ['$diagramRenderer' => service(PlantumlRenderer::class)],
        )
        ->tag('twig.extension')
        ->set(UmlDirective::class);
};
