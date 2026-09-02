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

namespace phpDocumentor\Guides\RestructuredText\Directives\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Directive
{
    /**
     * @param string[] $aliases
     * @param bool $synchronous Keep processing this directive synchronously at parse
     *     time via process()/processSub(), instead of deferring node creation to
     *     createNode() at compile time. Declaring name/aliases here still makes the
     *     directive discoverable by tooling that reads this attribute; set this when
     *     the directive's logic genuinely depends on parse-time-only state (e.g. the
     *     current BlockContext for logging, file paths, or raw/unparsed content) that
     *     createNode() has no access to.
     */
    public function __construct(
        public readonly string $name,
        public readonly array $aliases = [],
        public readonly bool $synchronous = false,
    ) {
    }
}
