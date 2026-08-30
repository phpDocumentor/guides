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

use phpDocumentor\Guides\Nodes\Index\GenIndexNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function array_filter;
use function array_map;
use function array_values;
use function explode;
use function trim;

/**
 * Renders a genindex term listing wherever it's placed, optionally scoped to
 * documents under one or more path prefixes -- unlike the project-wide
 * `:template: genindex` page, this can be dropped inline anywhere and used
 * more than once, e.g. one per version directory in a changelog:
 *
 * ```rest
 *   .. genindex::
 *
 *   .. genindex::
 *        :scope: Changelog/12.4/
 *
 *   .. genindex::
 *        :scope: Changelog/12.4/, Changelog/12.4-security/
 * ```
 *
 * The A-Z letter index (jumpbox + one heading per letter) can be turned off
 * for small, e.g. per-version, listings where it adds more noise than it
 * saves navigation -- terms are then listed flat, in one table:
 *
 * ```rest
 *   .. genindex::
 *        :scope: Changelog/12.4/
 *        :no-letter-index:
 * ```
 *
 * The node is populated later by IndexCollectorPass, once entries from every
 * document have been collected; at parse time it's an empty placeholder.
 */
final class GenIndexDirective extends BaseDirective
{
    public function getName(): string
    {
        return 'genindex';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node {
        $prefixes = explode(',', $directive->getOptionString('scope'));
        $prefixes = array_values(array_filter(array_map(trim(...), $prefixes), static fn (string $prefix): bool => $prefix !== ''));

        return new GenIndexNode([], $prefixes, !$directive->hasOption('no-letter-index'));
    }
}
