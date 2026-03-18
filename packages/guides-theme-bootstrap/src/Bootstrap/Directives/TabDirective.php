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

namespace phpDocumentor\Guides\Bootstrap\Directives;

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Guides\RestructuredText\Directives\TabDirective as RstTabDirective;

use function class_exists;

Deprecation::trigger(
    'phpDocumentor/guides-theme-bootstrap',
    'https://github.com/phpDocumentor/guides/issues/1320',
    'The "%s" class is deprecated, use "%s" instead.',
    TabDirective::class,
    RstTabDirective::class,
);

class_exists(RstTabDirective::class);

// @phpstan-ignore if.alwaysFalse
if (false) {
    final class TabDirective
    {
    }
}
