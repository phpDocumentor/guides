<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

/**
 * Titled Nodes handle anchors and provide a title to links. Therefore, a reference not supplying a title to them yields
 * in a link with a link text equaling this nodes title.
 */
interface TitledNode
{
    public function getTitlePlaintext(): string;

    public function addAnchor(string $anchor): void;

    /** @return string[] */
    public function getAnchors(): array;
}
