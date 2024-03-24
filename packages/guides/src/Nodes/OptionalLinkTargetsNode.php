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
 * Makes the implementing node an optional link target. If Noindex is true
 * no references are generated, there is no entry in the objects index and no
 * warning about duplicate ids.
 *
 * Used for example in https://sphinx-toolbox.readthedocs.io/en/stable/extensions/confval.html#directive-option-confval-noindex
 */
interface OptionalLinkTargetsNode extends LinkTargetNode
{
    public function isNoindex(): bool;
}
