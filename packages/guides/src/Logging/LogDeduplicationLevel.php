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

namespace phpDocumentor\Guides\Logging;

/**
 * How aggressively {@see DeduplicatingLogger} collapses repeated log
 * messages, configurable per project via guides.xml's `log-deduplication`
 * attribute.
 */
enum LogDeduplicationLevel: string
{
    /** Never deduplicate; every call reaches the underlying logger. */
    case None = 'none';

    /**
     * Deduplicate only an exact repeat: same level, message and context.
     * Catches a warning firing once per configured output format for the
     * same page, but not the same problem recurring on different pages.
     */
    case Exact = 'exact';

    /**
     * Deduplicate by level and message alone, ignoring context. Also
     * collapses the same problem recurring on different pages, at the cost
     * of losing per-page context (e.g. which of several pages referencing a
     * missing image gets named in the log) for anything not already baked
     * into the message text itself.
     */
    case Message = 'message';
}
