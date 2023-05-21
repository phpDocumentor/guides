<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\InlineToken\AbbreviationToken;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
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

    public function processNode(
        ParserContext $parserContext,
        string $id,
        string $role,
        string $content,
    ): InlineMarkupToken {
        if (preg_match('/([^\(]+)\(([^\)]+)\)$/', $content, $matches) !== 0) {
            return new AbbreviationToken($id, trim($matches[1]), trim($matches[2]));
        }

        $this->logger->warning(
            'Abbreviation has no definition. Usage: :abbreviation:`term (some term definition)`',
            $parserContext->getLoggerInformation(),
        );

        return new AbbreviationToken($id, $content, '');
    }
}
