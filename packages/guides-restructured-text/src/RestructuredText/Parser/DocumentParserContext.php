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

namespace phpDocumentor\Guides\RestructuredText\Parser;

use phpDocumentor\Guides\ParserContext;
use RuntimeException;
use ArrayObject;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\RestructuredText\Directives\Directive as DirectiveHandler;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;

use function md5;

/**
 * Our document parser contains
 */
class DocumentParserContext
{
    /** @var bool public is temporary */
    public $nextIndentedBlockShouldBeALiteralBlock = false;

    public ?DocumentNode $document = null;

    private LinesIterator $documentIterator;
    private ParserContext $context;
    private MarkupLanguageParser $markupLanguageParser;

    private int $currentTitleLevel;

    /** @var string[] */
    private array $titleLetters = [];

    /**
     * @param DirectiveHandler[] $directives
     */
    public function __construct(
        string $content,
        ParserContext $context,
        MarkupLanguageParser $markupLanguageParser
    ) {
        $this->documentIterator = new LinesIterator();
        $this->documentIterator->load($content);
        $this->context = $context;
        $this->markupLanguageParser = $markupLanguageParser;
        $this->currentTitleLevel = $context->getInitialHeaderLevel() - 1;
    }

    public function getContext(): ParserContext
    {
        return $this->context;
    }

    public function getParser(): MarkupLanguageParser
    {
        return $this->markupLanguageParser;
    }

    public function getDocument(): DocumentNode
    {
        if ($this->document === null) {
            throw new RuntimeException('Cannot get document, parser is not started');
        }

        return $this->document;
    }

    public function setDocument(DocumentNode $document): void
    {
        $this->document = $document;
    }

    public function getDocumentIterator(): LinesIterator
    {
        return $this->documentIterator;
    }

    public function getLevel(string $letter): int
    {
        foreach ($this->titleLetters as $level => $titleLetter) {
            if ($letter === $titleLetter) {
                return $level;
            }
        }

        $this->currentTitleLevel++;
        $this->titleLetters[$this->currentTitleLevel] = $letter;

        return $this->currentTitleLevel;
    }

    public function withContents(string $contents): self
    {
        $that = clone $this;
        $that->documentIterator->load($contents);

        return $that;
    }
}
