<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbbreviationInlineNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\ParserContext;
use Psr\Log\LoggerInterface;

use function preg_match;
use function trim;

class AbbreviationTextRole implements TextRole
{
    final public const NAME = 'abbreviation';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /** @inheritDoc */
    public function getAliases(): array
    {
        return [];
    }

    /** @return AbbreviationInlineNode */
    public function processNode(
        ParserContext $parserContext,
        string $role,
        string $content,
        string $rawContent,
    ): InlineNode {
        if (preg_match('/([^\(]+)\(([^\)]+)\)$/', $content, $matches) !== 0) {
            return new AbbreviationInlineNode(trim($matches[1]), trim($matches[2]));
        }

        $this->logger->warning(
            'Abbreviation has no definition. Usage: :abbreviation:`term (some term definition)`',
            $parserContext->getLoggerInformation(),
        );

        return new AbbreviationInlineNode($content, '');
    }
}
