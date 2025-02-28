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

namespace phpDocumentor\Guides\Cli\Command;

use phpDocumentor\Guides\Event\PostCollectFilesForParsingEvent;
use phpDocumentor\Guides\Event\PostParseDocument;
use phpDocumentor\Guides\Event\PostParseProcess;
use phpDocumentor\Guides\Event\PostRenderDocument;
use phpDocumentor\Guides\Event\PostRenderProcess;
use phpDocumentor\Guides\Event\PreParseDocument;
use phpDocumentor\Guides\Event\PreRenderDocument;
use phpDocumentor\Guides\Event\PreRenderProcess;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function count;
use function microtime;
use function sprintf;

/** @internal  */
final class ProgressBarSubscriber
{
    public function subscribe(ConsoleOutputInterface $output, EventDispatcherInterface $dispatcher): void
    {
        $this->registerParserProgressBar($output, $dispatcher);
        $this->registerRenderProgressBar($output, $dispatcher);
    }

    private function registerParserProgressBar(ConsoleOutputInterface $output, EventDispatcherInterface $dispatcher): void
    {
        $parsingProgressBar = new ProgressBar($output->section());
        $parsingProgressBar->setFormat('Parsing: %current%/%max% [%bar%] %percent:3s%% %message%');
        $parsingStartTime = microtime(true);
        $dispatcher->addListener(
            PostCollectFilesForParsingEvent::class,
            static function (PostCollectFilesForParsingEvent $event) use ($parsingProgressBar, &$parsingStartTime): void {
                // Each File needs to be first parsed then rendered
                $parsingStartTime = microtime(true);
                $parsingProgressBar->setMaxSteps(count($event->getFiles()));
            },
        );
        $dispatcher->addListener(
            PreParseDocument::class,
            static function (PreParseDocument $event) use ($parsingProgressBar): void {
                $parsingProgressBar->setMessage('Parsing file: ' . $event->getFileName());
                $parsingProgressBar->display();
            },
        );
        $dispatcher->addListener(
            PostParseDocument::class,
            static function (PostParseDocument $event) use ($parsingProgressBar): void {
                $parsingProgressBar->advance();
            },
        );
        $dispatcher->addListener(
            PostParseProcess::class,
            static function (PostParseProcess $event) use ($parsingProgressBar, $parsingStartTime): void {
                $parsingTimeElapsed = microtime(true) - $parsingStartTime;
                $parsingProgressBar->setMessage(sprintf(
                    'Parsed %s files in %.2f seconds',
                    $parsingProgressBar->getMaxSteps(),
                    $parsingTimeElapsed,
                ));
                $parsingProgressBar->finish();
            },
        );
    }

    private function registerRenderProgressBar(ConsoleOutputInterface $output, EventDispatcherInterface $dispatcher): void
    {
        $dispatcher->addListener(
            PreRenderProcess::class,
            static function (PreRenderProcess $event) use ($dispatcher, $output): void {
                $renderingProgressBar = new ProgressBar($output->section(), count($event->getCommand()->getDocumentArray()));
                $renderingProgressBar->setFormat('Rendering: %current%/%max% [%bar%] %percent:3s%% Output format ' . $event->getCommand()->getOutputFormat() . ': %message%');
                $renderingStartTime = microtime(true);
                $dispatcher->addListener(
                    PreRenderDocument::class,
                    static function (PreRenderDocument $event) use ($renderingProgressBar): void {
                        $renderingProgressBar->setMessage('Rendering: ' . $event->getCommand()->getFileDestination());
                        $renderingProgressBar->display();
                    },
                );
                $dispatcher->addListener(
                    PostRenderDocument::class,
                    static function (PostRenderDocument $event) use ($renderingProgressBar): void {
                        $renderingProgressBar->advance();
                    },
                );
                $dispatcher->addListener(
                    PostRenderProcess::class,
                    static function (PostRenderProcess $event) use ($renderingProgressBar, $renderingStartTime): void {
                        $renderingElapsedTime = microtime(true) - $renderingStartTime;
                        $renderingProgressBar->setMessage(sprintf(
                            'Rendered %s documents in %.2f seconds',
                            $renderingProgressBar->getMaxSteps(),
                            $renderingElapsedTime,
                        ));
                        $renderingProgressBar->finish();
                    },
                );
            },
        );
    }
}
