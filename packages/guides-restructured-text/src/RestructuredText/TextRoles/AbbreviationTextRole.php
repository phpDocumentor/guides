<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbbreviationInlineNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use Psr\Log\LoggerInterface;

use function preg_match;
use function trim;

class AbbreviationTextRole extends BaseTextRole
{
    protected string $name = 'abbreviation';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return AbbreviationInlineNode */
    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): InlineNode {
        if (preg_match('/([^\(]+)\(([^\)]+)\)$/', $content, $matches) !== 0) {
            return new AbbreviationInlineNode(trim($matches[1]), trim($matches[2]));
        }

        $this->logger->warning(
            'Abbreviation has no definition. Usage: :abbreviation:`term (some term definition)`',
            $documentParserContext->getContext()->getLoggerInformation(),
        );

        return new AbbreviationInlineNode($content, '');
    }
}
