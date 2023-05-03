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

namespace phpDocumentor\Guides\RestructuredText\Directives\OptionMapper;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;

use function trim;

/**
 * The directives `code-block` and `literalinclude` both create a CodeNode with the same possible options.
 * This common mapper is used by both Directives.
 */
final class CodeNodeOptionMapper
{
    /** @param DirectiveOption[] $directiveOptions */
    public function apply(CodeNode $codeNode, array $directiveOptions): void
    {
        if (isset($directiveOptions['language'])) {
            $codeNode->setLanguage(trim((string) $directiveOptions['language']->getValue()));
        }

        $this->setStartingLineNumberBasedOnOptions($directiveOptions, $codeNode);
    }

    /** @param DirectiveOption[] $options */
    private function setStartingLineNumberBasedOnOptions(array $options, CodeNode $node): void
    {
        if (!isset($options['linenos'])) {
            return;
        }

        $startingLineNumber = 1;

        if (isset($options['lineno-start'])) {
            $startingLineNumber = (int) $options['lineno-start']->getValue();
        }

        $node->setStartingLineNumber((int) $startingLineNumber);
    }
}
