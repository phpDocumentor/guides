<?php

declare(strict_types=1);

use phpDocumentor\Guides\Event\PostRenderProcess;
use phpDocumentor\Guides\Search\Indexer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(Indexer::class)
        ->tag(
            'event_listener',
            ['event' => PostRenderProcess::class, 'method' => 'index'],
        );
};
