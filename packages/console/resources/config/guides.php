<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()->autowire()
        ->set('phpdoc.guides.format.html', \phpDocumentor\Guides\Formats\SimpleOutputFormat::class)
        ->arg('$fileExtension', 'html')
        ->tag('phpdoc.guides.format')

        ->set(phpDocumentor\Guides\Formats\OutputFormats::class)
        ->arg('$outputFormats', tagged_locator('phpdoc.guides.format'));


};
