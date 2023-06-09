<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\Nodes\InlineToken\ReferenceNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;
use Psr\Log\LoggerInterface;

use function sprintf;
use function trim;

class ReferenceTextRole implements TextRole
{
    final public const NAME = 'ref';
    private SpanLexer $lexer;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
        // Do not inject the $lexer. It contains a state.
        $this->lexer = new SpanLexer();
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

    /** @return ReferenceNode */
    public function processNode(
        ParserContext $parserContext,
        string $role,
        string $content,
    ): InlineMarkupToken {
        $domain = null;
        $text = null;
        $part = '';
        $this->lexer->setInput($content);
        $this->lexer->moveNext();
        $this->lexer->moveNext();
        while ($this->lexer->token !== null) {
            $token = $this->lexer->token;
            switch ($token->type) {
                case SpanLexer::EMBEDED_URL_START:
                    $text = trim(($domain ? $domain . ':' : '') . $part);
                    $part = '';
                    break;
                case SpanLexer::EMBEDED_URL_END:
                    if ($this->lexer->peek() !== null) {
                        $this->logger->warning(
                            sprintf(
                                'Reference contains unexpected content after closing `>`: "%s"',
                                $content,
                            ),
                            $parserContext->getLoggerInformation(),
                        );
                    }

                    break 2;
                case SpanLexer::COLON:
                    $domain = $part;
                    $part = '';
                    break;
                default:
                    $part .= $token->value;
            }

            $this->lexer->moveNext();
        }

        return new ReferenceNode(
            referenceName: trim($part),
            domain: $domain,
            text: $text,
        );
    }
}
