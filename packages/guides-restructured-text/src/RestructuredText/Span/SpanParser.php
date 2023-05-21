<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Span;

use phpDocumentor\Guides\Nodes\InlineToken\CitationInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\CrossReferenceNode;
use phpDocumentor\Guides\Nodes\InlineToken\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\InlineToken\InlineMarkupToken;
use phpDocumentor\Guides\Nodes\InlineToken\LiteralToken;
use phpDocumentor\Guides\Nodes\SpanNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use phpDocumentor\Guides\RestructuredText\Parser\AnnotationUtility;

use function implode;
use function is_array;
use function mt_getrandmax;
use function preg_replace;
use function preg_replace_callback;
use function random_int;
use function sha1;
use function str_replace;
use function stripslashes;
use function time;
use function trim;

class SpanParser
{
    private int $tokenId = 0;

    private readonly string $prefix;

    /** @var InlineMarkupToken[] */
    private array $tokens = [];

    public function __construct(
        private readonly TextRoleFactory $textRoleFactory
    )
    {
        $this->lexer = new SpanLexer();
        $this->prefix = random_int(0, mt_getrandmax()) . '|' . time();
    }

    /** @param string|string[] $span */
    public function parse(string|array $span, ParserContext $parserContext): SpanNode
    {
        $this->tokens = [];
        if (is_array($span)) {
            $span = implode("\n", $span);
        }

        return new SpanNode($this->process($parserContext, $span), $this->tokens);
    }

    private function process(ParserContext $parserContext, string $span): string
    {
        $span = $this->replaceLiterals($span);

        $this->lexer->setInput($span);
        $this->lexer->moveNext();
        $this->lexer->moveNext();

        $result = $this->parseTokens($parserContext);
        $result = $this->replaceStandaloneHyperlinks($result);
        $result = $this->replaceStandaloneEmailAddresses($result);

        return stripslashes($result);
    }

    /** @param string[] $tokenData */
    private function addToken(string $type, string $id, array $tokenData): void
    {
        $this->tokens[$id] = new InlineMarkupToken($type, $id, $tokenData);
    }

    private function replaceLiterals(string $span): string
    {
        return preg_replace_callback(
            '/``(.+)``(?!`)/mUsi',
            function (array $match): string {
                $id = $this->generateId();
                $this->tokens[$id] = new LiteralToken(
                    $id,
                    $match[1],
                );

                return $id;
            },
            $span,
        ) ?? '';
    }

    private function createNamedReference(ParserContext $parserContext, string $link, string|null $url = null): string
    {
        // the link may have a new line in it, so we need to strip it
        // before setting the link and adding a token to be replaced
        $link = str_replace("\n", ' ', $link);
        $link = trim(preg_replace('/\s+/', ' ', $link) ?? '');

        $id = $this->createOneOffLink($link, $url);

        if ($url !== null) {
            $parserContext->setLink($link, $url);
        }

        return $id;
    }

    private function createAnonymousReference(ParserContext $parserContext, string $link): string
    {
        $parserContext->resetAnonymousStack();
        $id = $this->createNamedReference($parserContext, $link);
        $parserContext->pushAnonymous($link);

        return $id;
    }

    private function createCrossReference(string $link): string
    {
        // the link may have a new line in it, so we need to strip it
        // before setting the link and adding a token to be replaced
        $link = str_replace("\n", ' ', $link);
        $link = trim(preg_replace('/\s+/', ' ', $link) ?? '');

        $id = $this->generateId();
        $this->tokens[$id] = new CrossReferenceNode(
            $id,
            'ref',
            $link,
        );

        return $id;
    }

    private function replaceStandaloneHyperlinks(string $span): string
    {
        // Replace standalone hyperlinks using a modified version of @gruber's
        // "Liberal Regex Pattern for all URLs", https://gist.github.com/gruber/249502
        $absoluteUriPattern = '#(?i)\b((?:[a-z][\w\-+.]+:(?:/{1,3}|[a-z0-9%]))('
            . '?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>'
            . ']+|(\([^\s()<>]+\)))*\)|[^\s\`!()\[\]{};:\'".,<>?«»“”‘’]))#';

        // Standalone hyperlink callback
        $standaloneHyperlinkCallback = function (array $match, string $scheme = ''): string {
            $id = $this->generateId();
            $url = $match[1];

            $this->addToken(
                InlineMarkupToken::TYPE_LINK,
                $id,
                [
                    'link' => $url,
                    'url' => $scheme . $url,
                ],
            );

            return $id;
        };

        return preg_replace_callback(
            $absoluteUriPattern,
            $standaloneHyperlinkCallback,
            $span,
        ) ?? '';
    }

    private function replaceStandaloneEmailAddresses(string $span): string
    {
        // Replace standalone email addresses using a regex based on RFC 5322.
        $emailAddressPattern = '/((?:[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9'
            . '!#$%&\'*+\/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x'
            . '23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z'
            . '0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|'
            . '\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2'
            . '[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0'
            . 'b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f'
            . '])+)\]))/msi';

        $standaloneEmailAddressCallback = function (array $match): string {
            $id = $this->generateId();
            $url = $match[1];

            $this->addToken(
                InlineMarkupToken::TYPE_LINK,
                $id,
                [
                    'link' => $url,
                    'url' => 'mailto:' . $url,
                ],
            );

            return $id;
        };

        return preg_replace_callback(
            $emailAddressPattern,
            $standaloneEmailAddressCallback,
            $span,
        ) ?? '';
    }

    private function generateId(): string
    {
        $this->tokenId++;

        return sha1($this->prefix . '|' . $this->tokenId);
    }

    private function parseTokens(ParserContext $parserContext): string
    {
        $result = '';
        while ($this->lexer->token !== null) {
            switch ($this->lexer->token->type ?? '') {
                case SpanLexer::NAMED_REFERENCE:
                    $result .= $this->createNamedReference(
                        $parserContext,
                        trim((string) $this->lexer->token->value, '_'),
                    );
                    break;
                case SpanLexer::ANONYMOUSE_REFERENCE:
                    $result .= $this->createAnonymousReference(
                        $parserContext,
                        trim((string) $this->lexer->token->value, '_'),
                    );
                    break;
                case SpanLexer::INTERNAL_REFERENCE_START:
                    $result .= $this->parseInternalReference($parserContext);
                    break;
                case SpanLexer::COLON:
                    $result .= $this->parseTextrole($parserContext);
                    break;
                case SpanLexer::BACKTICK:
                    $link = $this->parseNamedReference($parserContext);
                    $result .= $link;
                    break;
                case SpanLexer::NAMED_REFERENCE_END:
                    $result .= $this->createNamedReference($parserContext, $result);
                    break;
                case SpanLexer::ANNOTATION_START:
                    $result .= $this->parseAnnotation();
                    break;
                default:
                    $result .= $this->lexer->token->value;
                    break;
            }

            $this->lexer->moveNext();
        }

        return $result;
    }

    private function parseInternalReference(ParserContext $parserContext): string
    {
        $text = '';
        $this->lexer->moveNext();
        while ($this->lexer->token !== null) {
            $token = $this->lexer->token;
            switch ($token->type) {
                case SpanLexer::BACKTICK:
                    return $this->createNamedReference($parserContext, $text);

                default:
                    $text .= $token->value;
            }

            $this->lexer->moveNext();
        }

        return $text;
    }
    private function parseAnnotation(): string
    {
        if ($this->lexer->token === null) {
            return '[';
        }

        $startPosition = $this->lexer->token->position;
        $annotationName = '';

        $this->lexer->moveNext();

        while ($this->lexer->token !== null) {
            $token = $this->lexer->token;
            switch ($token->type) {
                case SpanLexer::ANNOTATION_END:
                    // `]`  found, look for `_`
                    if (!$this->lexer->moveNext()) {
                        break 2;
                    }

                    $token = $this->lexer->token;
                    if ($token->type === SpanLexer::UNDERSCORE) {
                        $id = $this->generateId();
                        if (AnnotationUtility::isFootnoteKey($annotationName)) {
                            $number = AnnotationUtility::getFootnoteNumber($annotationName);
                            $name = AnnotationUtility::getFootnoteName($annotationName);
                            $this->tokens[$id] = new FootnoteInlineNode(
                                $id,
                                $annotationName,
                                $name ?? '',
                                $number ?? 0,
                            );
                        } else {
                            $this->tokens[$id] = new CitationInlineNode(
                                $id,
                                $annotationName,
                                $annotationName,
                            );
                        }

                        return $id;
                    }

                    break 2;
                case SpanLexer::WHITESPACE:
                    // Annotation keys may not contain whitespace
                    break 2;
                default:
                    $annotationName .= $token->value;
            }

            if ($this->lexer->moveNext() === false && $this->lexer->token === null) {
                break;
            }
        }

        $this->rollback($startPosition);

        return '[';
    }

    private function parseTextrole(ParserContext $parserContext): string
    {
        if ($this->lexer->token === null) {
            return ':';
        }

        $startPosition = $this->lexer->token->position;
        $domain = null;
        $role = null;
        $part = '';
        $inText = false;

        $this->lexer->moveNext();

        while ($this->lexer->token !== null) {
            $token = $this->lexer->token;
            switch ($token->type) {
                case $token->type === SpanLexer::COLON && $inText === false:
                    if ($role !== null) {
                        $domain = $role;
                        $role = $part;
                        $part = '';
                        break;
                    }

                    $role = $part;
                    $part = '';
                    break;
                case SpanLexer::BACKTICK:
                    if ($role === null) {
                        break 2;
                    }

                    if ($inText) {
                        $id = $this->generateId();
                        $textRole = $this->textRoleFactory->getTextRole($role, $domain);
                        $fullRole = ($domain ? $domain . ':' : '') . $role;
                        $this->tokens[$id] = $textRole->processNode($parserContext, $id, $fullRole, $part);

                        return $id;
                    }

                    $inText = true;
                    break;
                case SpanLexer::WHITESPACE:
                    if (!$inText) {
                        // textroles may not contain whitespace, we are not in a textrole but have found a common colon
                        break 2;
                    }

                    $part .= $token->value;

                    break;
                default:
                    $part .= $token->value;
            }

            if ($this->lexer->moveNext() === false && $this->lexer->token === null) {
                break;
            }
        }

        $this->rollback($startPosition);

        return ':';
    }

    private function parseNamedReference(ParserContext $parserContext): string
    {
        if ($this->lexer->token === null) {
            return '`';
        }

        $startPosition = $this->lexer->token->position;
        $text = '';
        $url = null;
        $this->lexer->moveNext();

        while ($this->lexer->token !== null) {
            $token = $this->lexer->token;
            switch ($token->type) {
                case SpanLexer::BACKTICK:
                    if (trim($text) === '') {
                        $this->lexer->resetPosition($startPosition);
                        $this->lexer->moveNext();
                        $this->lexer->moveNext();

                        return '`';
                    }

                    return $this->createCrossReference($text);

                case SpanLexer::NAMED_REFERENCE_END:
                    return $this->createNamedReference($parserContext, $text, $url);

                case SpanLexer::PHRASE_ANONYMOUS_END:
                    return $this->createOneOffLink($text, $url);

                case SpanLexer::EMBEDED_URL_START:
                    $url = $this->parseEmbeddedUrl();
                    if ($url === null) {
                        $text .= '<';
                    }

                    break;
                default:
                    $text .= $token->value;
                    break;
            }

            if ($this->lexer->moveNext() === false && $this->lexer->token === null) {
                break;
            }
        }

        $this->lexer->resetPosition($startPosition);
        $this->lexer->moveNext();
        $this->lexer->moveNext();

        return '`';
    }

    private function parseEmbeddedUrl(): string|null
    {
        if ($this->lexer->token === null) {
            return null;
        }

        $startPosition = $this->lexer->token->position;
        $text = '';

        while ($this->lexer->moveNext()) {
            $token = $this->lexer->token;
            switch ($token->type) {
                case SpanLexer::NAMED_REFERENCE_END:
                    //We did not find the expected SpanLexer::EMBEDED_URL_END
                    $this->rollback($startPosition);

                    return null;

                case SpanLexer::EMBEDED_URL_END:
                    return $text;

                default:
                    $text .= $token->value;
            }
        }

        $this->rollback($startPosition);

        return null;
    }

    private function rollback(int $position): void
    {
        $this->lexer->resetPosition($position);
        $this->lexer->moveNext();
        $this->lexer->moveNext();
    }

    private function createOneOffLink(string $link, string|null $url): string
    {
        // the link may have a new line in it, so we need to strip it
        // before setting the link and adding a token to be replaced
        $link = str_replace("\n", ' ', $link);
        $link = trim(preg_replace('/\s+/', ' ', $link) ?? '');

        $id = $this->generateId();
        $this->addToken(
            InlineMarkupToken::TYPE_LINK,
            $id,
            [
                'type' => InlineMarkupToken::TYPE_LINK,
                'link' => $link,
                'url' => $url ?? '',
            ],
        );

        return $id;
    }
}
