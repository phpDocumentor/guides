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

namespace phpDocumentor\Guides\RestructuredText\Directives;

/**
 * The kind of value a directive accepts right after `::`, declared via
 * {@see \phpDocumentor\Guides\RestructuredText\Directives\Attributes\Directive::$valueType}.
 *
 * Options have their own {@see OptionType}: what a directive's value may be
 * and what an option's value may be can differ, so the two are kept apart.
 */
enum DirectiveValueType
{
    /** No value is expected; one supplied anyway is likely a mistake. */
    case Empty;

    /** A raw string, used as-is -- never parsed as inline markup. */
    case String;

    /** Parsed as inline markup (bold, links, roles, ...), like body text. */
    case Inline;

    case Integer;

    case Boolean;

    /** A file or asset path, resolved relative to the current document. */
    case Path;

    /** An external URL. */
    case Url;

    /** A comma-separated list of scalar values. */
    case Array;
}
