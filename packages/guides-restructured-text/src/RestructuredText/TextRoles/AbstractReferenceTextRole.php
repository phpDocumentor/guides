<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;
use Psr\Log\LoggerInterface;

use function sprintf;
use function trim;

/** @see https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#embedded-uris-and-aliases */
abstract class AbstractReferenceTextRole implements TextRole
{
    private readonly InlineLexer $lexer;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
        // Do not inject the $lexer. It contains a state.
        $this->lexer = new InlineLexer();
    }

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): AbstractLinkInlineNode {
        $referenceTarget = null;
        $value = null;

        $part = '';
        $this->lexer->setInput($content);
        $this->lexer->moveNext();
        $this->lexer->moveNext();
        while ($this->lexer->token !== null) {
            $token = $this->lexer->token;
            switch ($token->type) {
                case InlineLexer::EMBEDED_URL_START:
                    $value = trim($part);
                    $part = '';

                    break;
                case InlineLexer::EMBEDED_URL_END:
                    if ($value === null) {
                        // not inside the embedded URL
                        $part .= $token->value;
                        break;
                    }

                    if ($this->lexer->peek() !== null) {
                        $this->logger->warning(
                            sprintf(
                                'Reference contains unexpected content after closing `>`: "%s"',
                                $content,
                            ),
                            $documentParserContext->getLoggerInformation(),
                        );
                    }

                    $referenceTarget = $part;
                    $part = '';

                    break 2;
                default:
                    $part .= $token->value;
            }

            $this->lexer->moveNext();
        }

        $value .= trim($part);

        if ($referenceTarget === null) {
            $referenceTarget = $value;
            $value = null;
        }

        return $this->createNode($referenceTarget, $value);
    }

    abstract protected function createNode(string $referenceTarget, string|null $referenceName): AbstractLinkInlineNode;
}
