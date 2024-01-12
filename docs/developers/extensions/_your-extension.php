<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use YourVendor\YourExtension\Directives\SomeDirective;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->set(SomeDirective::class)
        ->tag('phpdoc.guides.directive');
};
