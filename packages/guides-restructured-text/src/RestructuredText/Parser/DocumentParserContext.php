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

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use RuntimeException;

/**
 * Our document parser contains
 */
class DocumentParserContext
{
    /** @var bool public is temporary */
    public bool $nextIndentedBlockShouldBeALiteralBlock = false;

    public DocumentNode|null $document = null;
    
    private int $currentTitleLevel;
    /* Each Document has its own text role factory as text roles can be changed on a per document base
        by directives */
    private TextRoleFactory $textRoleFactoryForDocument;

    /** @var string[] */
    private array $titleLetters = [];

    public function __construct(
        private readonly ParserContext $context,
        TextRoleFactory $textRoleFactory,
        private readonly MarkupLanguageParser $markupLanguageParser,
    ) {
        $this->textRoleFactoryForDocument = clone $textRoleFactory;
        $this->currentTitleLevel = $context->getInitialHeaderLevel() - 1;
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->context->getProjectNode();
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
    
    public function getLevel(string $overlineLetter, string $underlineLetter): int
    {
        $letter = $overlineLetter . ':' . $underlineLetter;
        foreach ($this->titleLetters as $level => $titleLetter) {
            if ($letter === $titleLetter) {
                return $level;
            }
        }

        $this->currentTitleLevel++;
        $this->titleLetters[$this->currentTitleLevel] = $letter;

        return $this->currentTitleLevel;
    }

    public function getTextRoleFactoryForDocument(): TextRoleFactory
    {
        return $this->textRoleFactoryForDocument;
    }
}
