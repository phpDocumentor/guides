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
 * What a language tag looks like, for everything that accepts one.
 *
 * A document, a container and the "lang" role all take the language their
 * text is written in, and all three have to decide whether what the author
 * wrote is a language tag at all. The shape is the same in every case and
 * lives here once.
 */
interface Language
{
    /**
     * A BCP 47 language tag: "en", "nb", "en-US", "zh-Hant-TW".
     *
     * Deliberately the shape of a tag rather than the registry of real ones:
     * a documentation build is in no position to tell an author that their
     * language does not exist, only that what they wrote cannot be one.
     */
    public const PATTERN = '/^[a-zA-Z]{2,8}(-[a-zA-Z0-9]{1,8})*$/';
}
