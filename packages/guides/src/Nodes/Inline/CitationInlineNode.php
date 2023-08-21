<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

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
    final public const TYPE = 'citation_inline';

    public function __construct(string $value, private readonly string $name)
    {
        parent::__construct(self::TYPE, $value);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
