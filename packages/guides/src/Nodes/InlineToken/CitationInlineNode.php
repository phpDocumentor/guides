<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

/**
 * This node represents a citation inline a text
 *
 * Lorem ipsum [Ref]_ dolor sit amet.
 *
 * .. [Ref] Book or article reference, URL or whatever.
 *
 * https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html#citations
 */
class CitationInlineNode extends AnnotationInlineNode
{
    public const TYPE = 'citation_inline';

    public function __construct(string $id, string $value, private string $name)
    {
        parent::__construct(self::TYPE, $id, $value);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
