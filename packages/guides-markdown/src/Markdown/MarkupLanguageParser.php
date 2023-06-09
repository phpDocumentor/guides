<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown;

use League\CommonMark\Environment\Environment as CommonMarkEnvironment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Parser\MarkdownParser;
use phpDocumentor\Guides\Markdown\Parsers\ListBlock;
use phpDocumentor\Guides\Markdown\Parsers\Paragraph;
use phpDocumentor\Guides\Markdown\Parsers\ThematicBreak;
use phpDocumentor\Guides\MarkupLanguageParser as MarkupLanguageParserInterface;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\RawNode;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\ParserContext;
use RuntimeException;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function md5;
use function strtolower;

final class MarkupLanguageParser implements MarkupLanguageParserInterface
{
    private readonly MarkdownParser $markdownParser;

    private ParserContext|null $parserContext = null;

    /** @var ParserInterface<Node>[] */
    private readonly array $parsers;

    private DocumentNode|null $document = null;
    private readonly AsciiSlugger $idGenerator;

    public function __construct()
    {
        $cmEnvironment = new CommonMarkEnvironment(['html_input' => 'strip']);
        $cmEnvironment->addExtension(new CommonMarkCoreExtension());
        $this->markdownParser = new MarkdownParser($cmEnvironment);
        $this->idGenerator = new AsciiSlugger();
        $this->parsers = [
            new Paragraph(),
            new ListBlock(),
            new ThematicBreak(),
        ];
    }

    public function supports(string $inputFormat): bool
    {
        return strtolower($inputFormat) === 'md';
    }

    public function parse(ParserContext $parserContext, string $contents): DocumentNode
    {
        $this->parserContext = $parserContext;

        $ast = $this->markdownParser->parse($contents);

        return $this->parseDocument($ast->walker(), md5($contents));
    }

    private function parseDocument(NodeWalker $walker, string $hash): DocumentNode
    {
        $document = new DocumentNode($hash, $this->getParserContext()->getCurrentAbsolutePath());
        $this->document = $document;

        while ($event = $walker->next()) {
            $node = $event->getNode();

            foreach ($this->parsers as $parser) {
                if (!$parser->supports($event)) {
                    continue;
                }

                $document->addChildNode($parser->parse($this, $walker));
            }

            // ignore all Entering events; these are only used to switch to another context and context switching
            // is defined above
            if ($event->isEntering()) {
                continue;
            }

            if ($node instanceof Document) {
                return $document;
            }

            if ($node instanceof Heading) {
                $content = $node->firstChild();
                if ($content instanceof Text === false) {
                    continue;
                }

                $title = new TitleNode(
                    InlineCompoundNode::getPlainTextInlineNode($content->getLiteral()),
                    $node->getLevel(),
                    $this->idGenerator->slug($content->getLiteral())->lower()->toString(),
                );
                $document->addChildNode($title);
                continue;
            }

            if ($node instanceof Text) {
                $spanNode = new SpanNode($node->getLiteral(), []);
                $document->addChildNode($spanNode);
                continue;
            }

            if ($node instanceof Code) {
                $spanNode = new CodeNode([$node->getLiteral()]);
                $document->addChildNode($spanNode);
                continue;
            }

            if ($node instanceof Link) {
                $spanNode = new AnchorNode($node->getUrl());
                $document->addChildNode($spanNode);
                continue;
            }

            if ($node instanceof FencedCode) {
                $spanNode = new CodeNode([$node->getLiteral()]);
                $document->addChildNode($spanNode);
                continue;
            }

            if ($node instanceof HtmlBlock) {
                $spanNode = new RawNode($node->getLiteral());
                $document->addChildNode($spanNode);
                continue;
            }

            echo 'DOCUMENT CONTEXT: I am '
                . 'leaving'
                . ' a '
                . $node::class
                . ' node'
                . "\n";
        }

        return $document;
    }

    public function parseParagraph(NodeWalker $walker): ParagraphNode
    {
        return (new Paragraph())->parse($this, $walker);
    }

    public function parseListBlock(NodeWalker $walker): ListNode
    {
        return (new ListBlock())->parse($this, $walker);
    }

    public function getParserContext(): ParserContext
    {
        if ($this->parserContext === null) {
            throw new RuntimeException(
                'A parser\'s Environment should not be consulted before parsing has started',
            );
        }

        return $this->parserContext;
    }

    public function getDocument(): DocumentNode
    {
        if ($this->document === null) {
            throw new RuntimeException('Cannot get document as parser is not started');
        }

        return $this->document;
    }
}
