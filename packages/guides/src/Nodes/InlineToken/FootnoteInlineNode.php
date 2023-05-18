<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

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
    public const TYPE = 'footnote';

    public function __construct(string $id, string $value, private string $name, private int $number)
    {
        parent::__construct(self::TYPE, $id, $value);
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
