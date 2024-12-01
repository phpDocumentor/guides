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

use function func_get_arg;
use function func_num_args;
use function is_string;

abstract class AbstractLinkInlineNode extends InlineCompoundNode implements LinkInlineNode
{
    use BCInlineNodeBehavior;

    private string $url = '';

    /** @param InlineNodeInterface[] $children */
    public function __construct(
        private readonly string $type,
        private readonly string $targetReference,
        string|array $children = [],
    ) {
        if (is_string($children)) {
            Deprecation::trigger(
                'phpdocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1161',
                'Passing the content of %s as string is deprecated, pass an array of InlineNodeInterface instances instead. New signature: string $type, string $targetReference, array $children',
                static::class,
            );

            if (func_num_args() < 4) {
                // compat with (string $type, string $targetReference, string $value) signature
                $children = $children === '' ? [] : [new PlainTextInlineNode($children)];
            } else {
                // compat with (string $type, string $targetReference, string $value, array $children = []) signature
                /** @var InlineNodeInterface[] $children */
                $children = func_get_arg(3);
            }
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
            'value' => $this->toString(),
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }
}
