<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\InlineToken\DocReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Span\SpanLexer;
use Psr\Log\LoggerInterface;

use function sprintf;
use function trim;

class DocReferenceTextRole implements TextRole
{
    final public const NAME = 'doc';

    public function __construct(
        private SpanLexer $lexer,
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
        $anchor = null;
        $text = null;
        $domain = null;
        $part = '';
        $this->lexer->setInput($content);
        $this->lexer->moveNext();
        $this->lexer->moveNext();
        while ($this->lexer->token !== null) {
            $token = $this->lexer->token;
            switch ($token->type) {
                case SpanLexer::EMBEDED_URL_START:
                    $text = trim(($domain ? $domain . ':' : '') . $part);
                    $domain = null;
                    $part = '';
                    break;
                case SpanLexer::EMBEDED_URL_END:
                    if ($this->lexer->moveNext() !== false) {
                        $this->logger->warning(sprintf(
                            'File %s reference contains unexpected content: "%s"',
                            $parserContext->getCurrentFileName(),
                            $content,
                        ));
                    }

                    break 2;
                case SpanLexer::COLON:
                    $domain = $part;
                    $part = '';
                    break;
                case SpanLexer::OCTOTHORPE:
                    $anchor = $this->parseAnchor();
                    break;
                default:
                    $part .= $token->value;
            }

            $this->lexer->moveNext();
        }

        return new DocReferenceNode(
            id: $id,
            documentLink: trim($part),
            anchor: $anchor,
            domain: $domain,
            text: $text,
        );
    }

    private function parseAnchor(): string
    {
        $anchor = '';
        $this->lexer->moveNext();
        while ($this->lexer->token !== null) {
            $token = $this->lexer->token;
            if ($token == null) {
                break;
            }

            switch ($token->type) {
                case SpanLexer::BACKTICK:
                case SpanLexer::EMBEDED_URL_END:
                    $this->lexer->resetPosition($token->position);

                    return $anchor;

                default:
                    $anchor .= $token->value;
                    break;
            }

            $this->lexer->moveNext();
        }

        return $anchor;
    }
}
