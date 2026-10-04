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
 * Implement this interface on a Node to let the compiler set the default highlight
 * language on it, as configured by the project settings or the reStructuredText
 * `.. highlight::` directive, whenever the node does not already have an explicit
 * language set.
 */
interface ConsumesDefaultHighlightLanguage
{
    public function getLanguage(): string|null;

    public function setLanguage(string|null $language): void;
}
