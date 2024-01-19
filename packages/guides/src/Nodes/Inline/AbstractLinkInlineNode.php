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

abstract class AbstractLinkInlineNode extends InlineNode implements LinkInlineNode
{
    private string $url = '';

    public function __construct(string $type, private readonly string $targetReference, string $value = '')
    {
        parent::__construct($type, $value);
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
}
