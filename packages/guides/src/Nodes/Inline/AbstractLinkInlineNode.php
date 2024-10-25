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

namespace phpDocumentor\Guides\Nodes\Inline;

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;

abstract class AbstractLinkInlineNode extends InlineCompoundNode implements LinkInlineNode
{
    use BCInlineNodeBehavior;

    private string $url = '';

    /** @param InlineNodeInterface[] $children */
    public function __construct(
        private readonly string $type,
        private readonly string $targetReference,
        string $value = '',
        array $children = [],
    ) {
        if (empty($children)) {
            Deprecation::trigger(
                'phpdocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1161',
                'Please provide the children as an array of InlineNodeInterface instances instead of a string.',
            );
            $children = [new PlainTextInlineNode($value)];
        }

        parent::__construct($children);
    }

    public function getTargetReference(): string
    {
        return $this->targetReference;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /** @return array<string, string> */
    public function getDebugInformation(): array
    {
        return [
            'type' => $this->getType(),
            'targetReference' => $this->getTargetReference(),
            'value' => $this->getValue(),
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }
}
