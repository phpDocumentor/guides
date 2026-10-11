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

namespace phpDocumentor\Guides\MachineReadable\DependencyInjection;

use phpDocumentor\Guides\Event\PostProjectNodeCreated;
use phpDocumentor\Guides\MachineReadable\EventListener\AddTocOutputFormat;
use phpDocumentor\Guides\MachineReadable\Renderer\DocumentOutputFiles;
use phpDocumentor\Guides\MachineReadable\Renderer\TocRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(MachineReadableExtension::class)]
final class MachineReadableExtensionTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();

        (new MachineReadableExtension())->load([], $this->container);
    }

    public function testRegistersTheFormatThatWritesTheTableOfContents(): void
    {
        $tags = $this->container->getDefinition(TocRenderer::class)->getTag('phpdoc.renderer.typerenderer');

        self::assertSame([['format' => 'llm_toc']], $tags);
    }

    public function testRendersTheTableOfContentsWithoutTheProjectNamingTheFormat(): void
    {
        $tags = $this->container->getDefinition(AddTocOutputFormat::class)->getTag('event_listener');

        self::assertSame([['event' => PostProjectNodeCreated::class]], $tags);
    }

    /**
     * Naming the files a page was rendered to is worth reusing, so the service
     * is public and its class name is its id. Anything else writing about
     * pages can inject it rather than keep a second list of output formats.
     */
    public function testExposesTheFilesAPageWasRenderedToAsAService(): void
    {
        self::assertTrue($this->container->getDefinition(DocumentOutputFiles::class)->isPublic());
    }
}
