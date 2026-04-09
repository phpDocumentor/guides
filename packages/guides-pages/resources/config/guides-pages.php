<?php

declare(strict_types=1);

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Event\PostParseProcess;
use phpDocumentor\Guides\Event\PostRenderProcess;
use phpDocumentor\Guides\FileCollector;
use phpDocumentor\Guides\Pages\EventListener\ParsePagesListener;
use phpDocumentor\Guides\Pages\EventListener\RenderPagesListener;
use phpDocumentor\Guides\Pages\NodeRenderers\Html\PageNodeRenderer;
use phpDocumentor\Guides\Pages\PagesRegistry;
use phpDocumentor\Guides\Pages\Renderer\PageRenderer;
use phpDocumentor\Guides\Pages\RestructeredText\Parser\Productions\FieldList\PageDestinationRule;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()

        ->set(PageRenderer::class)
        ->tag(
            'phpdoc.renderer.typerenderer',
            [
                'format'         => 'page',
                'noderender_tag' => 'phpdoc.guides.noderenderer.page',
            ],
        )
        ->args(['$commandBus' => service(CommandBus::class)])

        ->set(PageNodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.page')

        ->set(PagesRegistry::class)

        ->set(ParsePagesListener::class)
        ->args([
            '$commandBus'      => service(CommandBus::class),
            '$fileCollector'   => inline_service(FileCollector::class)->autowire(),
            '$sourceDirectory' => param('phpdoc.guides.pages.source_directory'),
        ])
        ->tag('event_listener', ['event' => PostParseProcess::class])

        ->set(RenderPagesListener::class)
        ->tag('event_listener', ['event' => PostRenderProcess::class])

        ->set(PageDestinationRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist');
};
