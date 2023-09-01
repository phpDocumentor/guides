<?php

declare(strict_types=1);

use phpDocumentor\Guides\Graphs\Directives\UmlDirective;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->instanceof(BaseDirective::class)
        ->tag('phpdoc.guides.directive')
        ->set(UmlDirective::class);
};
