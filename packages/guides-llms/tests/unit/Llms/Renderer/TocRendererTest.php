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

namespace phpDocumentor\Guides\Llms\Renderer;

use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\Llms\Event\ModifyTocPage;
use phpDocumentor\Guides\Llms\Event\ModifyTocProject;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ReferenceResolvers\SluggerAnchorNormalizer;
use phpDocumentor\Guides\Renderer\TypeRendererFactory;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function array_keys;
use function assert;
use function is_array;
use function json_decode;

#[CoversClass(TocRenderer::class)]
final class TocRendererTest extends TestCase
{
    private EventDispatcher $eventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = new EventDispatcher();
    }

    public function testNestsThePagesTheWayTheTableOfContentsDoes(): void
    {
        $toc = $this->render($this->project());

        self::assertSame(
            [
                [
                    'path' => 'index',
                    'title' => 'Handbook',
                    'anchor' => 'handbook-start',
                    'pages' => [
                        [
                            'path' => 'Chapter/Index',
                            'title' => 'Chapter',
                            'anchor' => 'chapter',
                            'pages' => [
                                [
                                    'path' => 'Chapter/Page',
                                    'title' => 'A page',
                                    'anchor' => 'a-page',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'orphan' => true,
                    'path' => 'Orphan',
                    'title' => 'Orphan',
                    'anchor' => 'orphan',
                ],
            ],
            $toc['pages'],
        );
    }

    public function testStatesWhatTheProjectSaysAboutItselfOnce(): void
    {
        $toc = $this->render($this->project());

        self::assertSame(['title' => 'Handbook', 'version' => '2.0'], $toc['project']);
    }

    /**
     * What the TYPO3 theme needs the project section for: a permalink pattern
     * of its own, and a version it spells differently.
     */
    public function testAListenerCanAddToAndCorrectTheProjectSection(): void
    {
        $this->eventDispatcher->addListener(
            ModifyTocProject::class,
            static function (ModifyTocProject $event): void {
                $project = $event->getProject();
                $project['version'] = 'main';
                $project['permalink'] = 'https://example.org/permalink/handbook:{anchor}';
                $event->setProject($project);
            },
        );

        $toc = $this->render($this->project());

        self::assertSame(
            [
                'title' => 'Handbook',
                'version' => 'main',
                'permalink' => 'https://example.org/permalink/handbook:{anchor}',
            ],
            $toc['project'],
        );
    }

    /** A listener sees every page, the orphans included, and the page's nodes. */
    public function testAListenerCanAddToEveryPage(): void
    {
        $this->eventDispatcher->addListener(
            ModifyTocPage::class,
            static function (ModifyTocPage $event): void {
                $page = $event->getPage();
                $page['orphan_page'] = $event->getDocumentEntry()->isOrphan();
                $page['parsed'] = $event->getDocument() !== null;
                $event->setPage($page);
            },
        );

        $toc = $this->render($this->project());

        $pages = $toc['pages'];
        assert(is_array($pages));

        self::assertSame(false, $pages[0]['orphan_page']);
        self::assertSame(true, $pages[0]['parsed']);
        self::assertSame(true, $pages[1]['orphan_page']);
        self::assertSame('a-page', $pages[0]['pages'][0]['pages'][0]['anchor']);
        self::assertSame(false, $pages[0]['pages'][0]['pages'][0]['orphan_page']);
    }

    /**
     * The children are nested after the event, so a listener adding a key
     * cannot push the subtree into the middle of the page it opens.
     */
    public function testThePagesOfAPageAreWrittenLast(): void
    {
        $this->eventDispatcher->addListener(
            ModifyTocPage::class,
            static function (ModifyTocPage $event): void {
                $event->setPage([...$event->getPage(), 'added' => true]);
            },
        );

        $toc = $this->render($this->project());

        self::assertSame(
            ['path', 'title', 'anchor', 'added', 'pages'],
            array_keys($toc['pages'][0]),
        );
    }

    /** @return array<string, mixed> */
    private function render(RenderCommand $renderCommand): array
    {
        $renderer = new TocRenderer(
            new SluggerAnchorNormalizer(),
            $this->outputFiles(),
            $this->eventDispatcher,
        );

        $renderer->render($renderCommand);

        $destination = $renderCommand->getDestination();
        assert($destination instanceof FileSystem);

        $toc = json_decode((string) $destination->read('toc.json'), true);
        assert(is_array($toc));

        return $toc;
    }

    /**
     * A project rendering nothing a page gets a file of its own from, so that
     * the shape under test is the shape this class produces.
     *
     * @see DocumentOutputFilesTest for the files themselves
     */
    private function outputFiles(): DocumentOutputFiles
    {
        $projectSettings = new ProjectSettings();
        $projectSettings->setOutputFormats([]);

        return new DocumentOutputFiles(
            new SettingsManager($projectSettings),
            $this->createStub(TypeRendererFactory::class),
        );
    }

    /**
     * A handbook of three pages in two levels, with one page no toctree
     * reaches.
     */
    private function project(): RenderCommand
    {
        $projectNode = new ProjectNode('Handbook', '2.0');

        // Only the index carries an explicit label; the others fall back on
        // the id their title was reduced to.
        $index = $this->page('index', 'Handbook', '', true, 'handbook-start');
        $chapter = $this->page('Chapter/Index', 'Chapter', 'chapter');
        $page = $this->page('Chapter/Page', 'A page', 'a-page');
        $orphan = $this->page('Orphan', 'Orphan', 'orphan', false, null, true);

        $index['entry']->addChild($chapter['entry']);
        $chapter['entry']->addChild($page['entry']);

        $projectNode->setDocumentEntries([$index['entry'], $chapter['entry'], $page['entry'], $orphan['entry']]);

        return new RenderCommand(
            TocRenderer::FORMAT,
            [$index['document'], $chapter['document'], $page['document'], $orphan['document']],
            FlySystemAdapter::createInMemory(),
            FlySystemAdapter::createInMemory(),
            $projectNode,
        );
    }

    /** @return array{entry: DocumentEntryNode, document: DocumentNode} */
    private function page(
        string $file,
        string $title,
        string $id,
        bool $isRoot = false,
        string|null $anchor = null,
        bool $orphan = false,
    ): array {
        $titleNode = new TitleNode(new InlineCompoundNode([new PlainTextInlineNode($title)]), 1, $id);

        $entry = new DocumentEntryNode($file, $titleNode, $isRoot, [], $orphan);

        $document = new DocumentNode('hash-' . $file, $file);
        $document->setDocumentEntry($entry);

        if ($anchor !== null) {
            $section = new SectionNode(TitleNode::fromString($title));
            $section->addChildNode(new AnchorNode($anchor));
            $document->addChildNode($section);
        }

        return ['entry' => $entry, 'document' => $document];
    }
}
