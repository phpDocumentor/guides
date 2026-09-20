<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

use phpDocumentor\Guides\Event\PostProjectNodeCreated;
use phpDocumentor\Guides\Llms\EventListener\AddTocOutputFormat;
use phpDocumentor\Guides\Llms\Renderer\DocumentOutputFiles;
use phpDocumentor\Guides\Llms\Renderer\TocRenderer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()

        // Public, and its class name is its service id: naming the files a
        // page was rendered to is not peculiar to the table of contents,
        // and anything else writing about pages should answer the question
        // the same way rather than keep a second list of formats.
        ->set(DocumentOutputFiles::class)
        ->public()

        ->set(TocRenderer::class)
        ->tag(
            'phpdoc.renderer.typerenderer',
            ['format' => TocRenderer::FORMAT],
        )

        ->set(AddTocOutputFormat::class)
        ->tag('event_listener', ['event' => PostProjectNodeCreated::class]);
};
