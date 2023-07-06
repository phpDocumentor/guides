<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

/**
 * This node represents a footnote inline a text
 *
 * Lorem ipsum [1]_ dolor sit amet ... [#f2]_ and an anonymous one: [#]_
 *
 * Yet another anonymous footnote: [#]_
 *
 * .. rubric:: Footnotes
 *
 * .. [1] Text of the first footnote.
 * .. [#f2] Text of the second footnote.
 * .. [#] Anonymous footnote
 * .. [#] Yet another Anonymous footnote
 *
 * https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html#footnotes
 */
class FootnoteInlineNode extends AnnotationInlineNode
{
    final public const TYPE = 'footnote';

    public function __construct(string $value, private readonly string $name, private readonly int $number)
    {
        parent::__construct(self::TYPE, $value);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}
