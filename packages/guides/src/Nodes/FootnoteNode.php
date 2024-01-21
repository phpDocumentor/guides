<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Nodes;

/**
 * Defines a footnote that can be referenced by an FootnoteInlineNode
 *
 * Lorem ipsum [#f1]_ dolor sit amet ... [#f2]_
 *
 * .. rubric:: Footnotes
 *
 * .. [#f1] Text of the first footnote.
 * .. [#f2] Text of the second footnote.
 */
final class FootnoteNode extends AnnotationNode
{
    /** @param list<InlineCompoundNode> $value */
    public function __construct(array $value, string $name, private int $number)
    {
        parent::__construct($value, $name);
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getAnchor(): string
    {
        return 'footnote-' . $this->number;
    }
}
