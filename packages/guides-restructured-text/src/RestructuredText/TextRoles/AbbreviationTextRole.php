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
        $term       = '';
        $definition = '';
        if (preg_match('/(.+)\(([^\)]+)\)$/', $content, $matches) !== 0) {
            $term       =  trim($matches[1]);
            $definition = trim($matches[2]);
        } else {
            $this->logger->warning(
                'Abbreviation has not definition. Usage: :abbreviation:`term (some term definition)`',
                $parserContext->getLoggerInformation(),
            );
            $term = $content;
        }

        return new AbbreviationToken($id, $term, $definition);
    }
}
