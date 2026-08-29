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

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\OptionMapper\CodeNodeOptionMapper;
use phpDocumentor\Guides\RestructuredText\Directives\OptionMapper\DefaultCodeNodeOptionMapper;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function array_slice;
use function array_values;
use function count;
use function explode;
use function is_string;
use function ksort;
use function max;
use function min;
use function preg_match;
use function sprintf;
use function str_contains;
use function trim;

final class LiteralincludeDirective extends BaseDirective
{
    public function __construct(
        private readonly CodeNodeOptionMapper $codeNodeOptionMapper,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    public function getName(): string
    {
        return 'literalinclude';
    }

    /** {@inheritDoc} */
    public function processNode(
        BlockContext $blockContext,
        Directive $directive,
    ): Node {
        $parser = $blockContext->getDocumentParserContext()->getParser();
        $parserContext = $parser->getParserContext();
        $path = $parserContext->absoluteRelativePath($directive->getData());

        $origin = $parserContext->getOrigin();
        if (!$origin->has($path)) {
            throw new RuntimeException(
                sprintf('Include "%s" (%s) does not exist or is not readable.', $directive->getData(), $path),
            );
        }

        $contents = $origin->read($path);

        if ($contents === false) {
            throw new RuntimeException(sprintf('Could not load file from path %s', $path));
        }

        $lines = $this->selectLines(explode("\n", $contents), $directive, $blockContext);

        $codeNode = new CodeNode($lines);
        $this->codeNodeOptionMapper->apply($codeNode, $directive->getOptions(), $blockContext);

        return $codeNode;
    }

    /**
     * Reduces the included file to the region enclosed by the ``start-after`` and ``end-before`` markers.
     *
     * The region starts on the line following the first line containing the ``start-after`` marker and ends
     * on the line preceding the first line containing the ``end-before`` marker. The ``end-before`` marker
     * is searched behind the start of the region, so the same marker text may be used more than once in a file.
     *
     * @param string[] $lines
     *
     * @return string[]
     */
    private function selectLines(array $lines, Directive $directive, BlockContext $blockContext): array
    {
        $start = 0;
        $end = count($lines);

        if ($directive->hasOption('start-after')) {
            $marker = $this->optionValue($directive, 'start-after', $blockContext);
            if ($marker === null) {
                return [];
            }

            $lineNumber = $this->findMarker($lines, $marker, $start);
            if ($lineNumber === null) {
                $this->warnMarkerNotFound($directive, 'start-after', $marker, $blockContext);

                return [];
            }

            $start = $lineNumber + 1;
        }

        if ($directive->hasOption('end-before')) {
            $marker = $this->optionValue($directive, 'end-before', $blockContext);
            if ($marker === null) {
                return [];
            }

            $lineNumber = $this->findMarker($lines, $marker, $start);
            if ($lineNumber === null) {
                if ($this->findMarker($lines, $marker, 0) === null) {
                    $this->warnMarkerNotFound($directive, 'end-before', $marker, $blockContext);
                } else {
                    $this->logger?->warning(
                        sprintf(
                            'Option ":end-before:" of directive "literalinclude": "%s" occurs in "%s" only above the line matched by ":start-after:", nothing was included.',
                            $marker,
                            $directive->getData(),
                        ),
                        $blockContext->getLoggerInformation(),
                    );
                }

                return [];
            }

            $end = $lineNumber;
        }

        $selection = array_slice($lines, $start, $end - $start);

        if ($selection === []) {
            $this->logger?->warning(
                sprintf(
                    'Directive "literalinclude": the region marked in "%s" is empty, nothing was included.',
                    $directive->getData(),
                ),
                $blockContext->getLoggerInformation(),
            );

            return [];
        }

        if ($directive->hasOption('lines')) {
            $selection = $this->selectLineRanges($selection, $directive, $blockContext);
        }

        return $selection;
    }

    /**
     * Reduces the included region to the line numbers listed in ``lines``, for example ``1,3-5,20-``.
     *
     * Line numbers are 1 based, ranges are inclusive and an omitted end means "up to the last line".
     * They count within the region selected by ``start-after`` and ``end-before``, not within the file.
     *
     * @param string[] $lines
     *
     * @return string[]
     */
    private function selectLineRanges(array $lines, Directive $directive, BlockContext $blockContext): array
    {
        $specification = $this->optionValue($directive, 'lines', $blockContext);
        if ($specification === null) {
            return [];
        }

        if (preg_match(DefaultCodeNodeOptionMapper::LINE_NUMBER_RANGES_REGEX, $specification) !== 1) {
            $this->logger?->warning(
                sprintf(
                    'Invalid value for option ":lines:" of directive "literalinclude": "%s". Expected format: \'1-5, 7, 33\'. Nothing was included.',
                    $specification,
                ),
                $blockContext->getLoggerInformation(),
            );

            return [];
        }

        $selected = [];
        foreach (explode(',', $specification) as $range) {
            $range = trim($range);
            [$first, $last] = $this->parseRange($range, count($lines));

            if ($first > $last) {
                $this->logger?->warning(
                    sprintf(
                        'Option ":lines:" of directive "literalinclude": the range "%s" selects no line of "%s".',
                        $range,
                        $directive->getData(),
                    ),
                    $blockContext->getLoggerInformation(),
                );

                continue;
            }

            for ($lineNumber = $first; $lineNumber <= $last; $lineNumber++) {
                $selected[$lineNumber] = $lines[$lineNumber - 1];
            }
        }

        ksort($selected);

        return array_values($selected);
    }

    /**
     * Splits a single entry of the ``lines`` option into its first and last line number.
     *
     * Both are clamped to the lines actually available, so that a range far beyond the end of the file
     * does not turn into a loop over the numbers the author wrote down. A first line greater than the
     * last one means the range selects nothing.
     *
     * @return array{int, int}
     */
    private function parseRange(string $range, int $lineCount): array
    {
        if (!str_contains($range, '-')) {
            $lineNumber = (int) $range;

            return [max(1, $lineNumber), min($lineNumber, $lineCount)];
        }

        [$first, $last] = explode('-', $range, 2);

        return [
            max(1, (int) $first),
            trim($last) === '' ? $lineCount : min((int) $last, $lineCount),
        ];
    }

    /** Returns the text of an option, or null if the option was used without a usable value. */
    private function optionValue(Directive $directive, string $option, BlockContext $blockContext): string|null
    {
        $value = $directive->getOption($option)->getValue();
        if (!is_string($value) || $value === '') {
            $this->logger?->warning(
                sprintf('Option ":%s:" of directive "literalinclude" requires a value, nothing was included.', $option),
                $blockContext->getLoggerInformation(),
            );

            return null;
        }

        return $value;
    }

    /**
     * Returns the number of the first line at or behind $offset that contains $marker, or null if there is none.
     *
     * @param string[] $lines
     */
    private function findMarker(array $lines, string $marker, int $offset): int|null
    {
        $lineCount = count($lines);
        for ($lineNumber = $offset; $lineNumber < $lineCount; $lineNumber++) {
            if (str_contains($lines[$lineNumber], $marker)) {
                return $lineNumber;
            }
        }

        return null;
    }

    private function warnMarkerNotFound(
        Directive $directive,
        string $option,
        string $marker,
        BlockContext $blockContext,
    ): void {
        $this->logger?->warning(
            sprintf(
                'Option ":%s:" of directive "literalinclude": no line containing "%s" was found in "%s", nothing was included.',
                $option,
                $marker,
                $directive->getData(),
            ),
            $blockContext->getLoggerInformation(),
        );
    }
}
