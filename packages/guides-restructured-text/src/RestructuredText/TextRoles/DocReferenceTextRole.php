<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;
use Psr\Log\LoggerInterface;

use function sprintf;
use function trim;

class DocReferenceTextRole implements TextRole
{
    final public const NAME = 'doc';
    private InlineLexer $lexer;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
        // Do not inject the $lexer. It contains a state.
        $this->lexer = new InlineLexer();
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

    /** @return DocReferenceNode */
    public function processNode(
        ParserContext $parserContext,
        string $role,
        string $content,
        string $rawContent,
    ): InlineNode {
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
                case InlineLexer::EMBEDED_URL_START:
                    $text = trim(($domain ? $domain . ':' : '') . $part);
                    $domain = null;
                    $part = '';
                    break;
                case InlineLexer::EMBEDED_URL_END:
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
                case InlineLexer::COLON:
                    $domain = $part;
                    $part = '';
                    break;
                case InlineLexer::OCTOTHORPE:
                    $anchor = $this->parseAnchor();
                    break;
                default:
                    $part .= $token->value;
            }

            $this->lexer->moveNext();
        }

        return new DocReferenceNode(
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

            switch ($token->type) {
                case InlineLexer::BACKTICK:
                case InlineLexer::EMBEDED_URL_END:
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
