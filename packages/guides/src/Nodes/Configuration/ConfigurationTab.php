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

namespace phpDocumentor\Guides\Nodes\Configuration;

use phpDocumentor\Guides\Nodes\CodeNode;

use function hash;

final class ConfigurationTab
{
    public readonly string $hash;

    public function __construct(
        public readonly string $label,
        public readonly string $slug,
        public readonly CodeNode $content,
    ) {
        $this->hash = hash('xxh128', $content->getValue());
    }
}
