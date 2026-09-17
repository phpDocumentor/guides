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
 * The kind of value expected in a single scalar slot of directive syntax --
 * either a directive's own value (the text right after `::`, declared via
 * {@see \phpDocumentor\Guides\RestructuredText\Directives\Attributes\Directive::$valueType})
 * or an individual option's value (declared via
 * {@see \phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option::$type}).
 * Both are the same kind of thing -- a single piece of text supplied inline
 * in the directive's opening syntax -- just attached at different places.
 */
enum ValueType
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
