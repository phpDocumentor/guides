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

use function strtolower;
use function trim;

/**
 * The direction a piece of text is read in, as the HTML "dir" attribute spells it.
 *
 * There are only three of them, so a document, a container or a piece of
 * inline text carries one of these rather than whatever string the author
 * happened to write. Anything else is refused where it is read, and the
 * reader is left with {@see TextDirection::Auto}: a direction the browser
 * works out for itself is a worse answer than the right one and a better
 * answer than a "dir" attribute that means nothing.
 */
enum TextDirection: string
{
    case Ltr = 'ltr';
    case Rtl = 'rtl';
    case Auto = 'auto';

    /**
     * The direction an author wrote, or null when it is none of them.
     *
     * Case and surrounding space are forgiven -- ":dir: RTL" means what it
     * looks like -- so that only a genuinely unknown direction is refused.
     */
    public static function tryFromUserInput(string $direction): self|null
    {
        return self::tryFrom(strtolower(trim($direction)));
    }
}
