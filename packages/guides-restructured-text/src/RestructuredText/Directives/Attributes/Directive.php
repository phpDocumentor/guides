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
     * @param bool $rawContent If set, the directive's body is captured as literal
     *     source text (DirectiveNode::getRawContent()) instead of being parsed into
     *     child nodes -- for directives whose content isn't reStructuredText (e.g.
     *     raw HTML/LaTeX passthrough). Parsing such content as RST would corrupt it
     *     (e.g. a line that looks like a section title gets misinterpreted), so it's
     *     skipped entirely rather than parsed and discarded.
     * @param bool $parseUndeclaredOptionsAsInlineMarkup If set, options that are not
     *     declared with a #[Option] attribute are parsed as inline markup during
     *     parsing, just like options explicitly declared with
     *     OptionType::InlineMarkup. Useful for directives that accept arbitrary,
     *     free-form options (e.g. confval's additional fields).
     */
    public function __construct(
        public readonly string $name,
        public readonly array $aliases = [],
        public readonly bool $rawContent = false,
        public readonly bool $parseUndeclaredOptionsAsInlineMarkup = false,
    ) {
    }
}
