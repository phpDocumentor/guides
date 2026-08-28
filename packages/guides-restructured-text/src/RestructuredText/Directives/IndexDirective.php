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

use phpDocumentor\Guides\Nodes\Index\IndexEntryNode;
use phpDocumentor\Guides\Nodes\Index\IndexEntryType;
use phpDocumentor\Guides\Nodes\Index\IndexNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use Psr\Log\LoggerInterface;

use function array_filter;
use function array_map;
use function array_values;
use function explode;
use function levenshtein;
use function mb_strpos;
use function mb_substr;
use function sprintf;
use function str_starts_with;
use function strtolower;
use function substr;
use function trim;

/**
 * Collects index entries, example:
 *
 * .. index:: single: installation
 *
 * .. index::
 *      single: configuration
 *      pair: configuration; file
 *      see: token; access token
 *      ! main entry example
 *
 * A line may also hold several comma-separated, type-less entries at once
 * (the convention used by e.g. TYPO3's Core Changelog files), each becoming
 * its own "single" entry:
 *
 * .. index:: Backend, PHP-API, NotScanned, ext:core
 *
 * The directive itself is invisible in the rendered page; entries are
 * collected project-wide to build the `genindex` page.
 *
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#directive-index
 */
final class IndexDirective extends BaseDirective
{
    /**
     * A colon-prefixed segment whose prefix is this close (or closer) to a
     * real type name is very likely a typo of it, e.g. "sindle:" -> "single:"
     * (distance 1). Anything further away, e.g. "ext:" in "ext:core", is
     * left alone as an intentional literal colon -- the comma-separated,
     * type-less form (see class docblock) routinely contains those.
     */
    private const TYPO_DISTANCE_THRESHOLD = 2;

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function getName(): string
    {
        return 'index';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $data = trim($directive->getData());
        $lines = $data !== '' ? [$data] : $blockContext->getDocumentIterator()->toArray();
        $lines = $this->trimAndFilterEmpty($lines);

        $segments = [];
        foreach ($lines as $line) {
            foreach (explode(',', $line) as $segment) {
                $segments[] = $segment;
            }
        }

        $segments = $this->trimAndFilterEmpty($segments);

        return new IndexNode(array_map(
            fn (string $segment): IndexEntryNode => $this->parseLine($segment, $blockContext),
            $segments,
        ));
    }

    /**
     * @param string[] $items
     *
     * @return string[]
     */
    private function trimAndFilterEmpty(array $items): array
    {
        $items = array_map(trim(...), $items);
        $items = array_filter($items, static fn (string $item): bool => $item !== '');

        return array_values($items);
    }

    private function parseLine(string $line, BlockContext $blockContext): IndexEntryNode
    {
        $type = IndexEntryType::Single;

        $colonPosition = mb_strpos($line, ':');
        if ($colonPosition !== false) {
            $candidate = strtolower(trim(mb_substr($line, 0, $colonPosition)));
            $candidateType = IndexEntryType::tryFrom($candidate);
            if ($candidateType !== null) {
                $type = $candidateType;
                $line = trim(mb_substr($line, $colonPosition + 1));
            } else {
                $this->warnIfLikelyTypo($candidate, $line, $blockContext);
            }
        }

        $main = false;
        if (str_starts_with($line, '!')) {
            $main = true;
            $line = trim(substr($line, 1));
        }

        $parts = array_map(trim(...), explode(';', $line));

        return new IndexEntryNode($type, $parts, $main);
    }

    private function warnIfLikelyTypo(string $candidate, string $line, BlockContext $blockContext): void
    {
        $closestType = null;
        $closestDistance = null;
        foreach (IndexEntryType::cases() as $case) {
            $distance = levenshtein($candidate, $case->value);
            if ($closestDistance !== null && $distance >= $closestDistance) {
                continue;
            }

            $closestType = $case;
            $closestDistance = $distance;
        }

        if ($closestType === null || $closestDistance > self::TYPO_DISTANCE_THRESHOLD) {
            return;
        }

        $this->logger->warning(
            sprintf(
                '.. index:: "%s:" is not a known entry type, did you mean "%s:"? Treating it as a literal term instead: "%s"',
                $candidate,
                $closestType->value,
                $line,
            ),
            $blockContext->getLoggerInformation(),
        );
    }
}
