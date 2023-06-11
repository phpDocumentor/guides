<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;
use Psr\Log\LoggerInterface;

use function sprintf;
use function trim;

class ReferenceTextRole implements TextRole
{
    final public const NAME = 'ref';
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

    /** @return ReferenceNode */
    public function processNode(
        ParserContext $parserContext,
        string $role,
        string $content,
        string $rawContent,
    ): InlineNode {
        $domain = null;
        $text = null;
        $part = '';
        $this->lexer->setInput($content);
        $this->lexer->moveNext();
        $this->lexer->moveNext();
        while ($this->lexer->token !== null) {
            $token = $this->lexer->token;
            switch ($token->type) {
                case InlineLexer::EMBEDED_URL_START:
                    $text = trim(($domain ? $domain . ':' : '') . $part);
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
