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

use phpDocumentor\Guides\Nodes\MathNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use Psr\Log\LoggerInterface;

use function explode;
use function preg_replace;

/**
 * Renders a code block, example:
 *
 * .. code-block:: php
 *
 *      <?php
 *
 *      echo "Hello world!\n";
 *
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#directive-code-block
 */
#[Attributes\Directive(name: 'math', rawContent: true)]
final class MathDirective extends BaseDirective
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function createNode(DirectiveNode $directiveNode): Node|null
    {
        // Matches LinesIterator::load()'s preserveSpace handling, which the old
        // dispatch's BlockContext ran the same raw content through -- only leading
        // and trailing blank lines are stripped, indentation within is untouched.
        $rawContent = (string) preg_replace('/^\n+/', '', $directiveNode->getRawContent());
        $rawContent = (string) preg_replace('/\n+$/', '', $rawContent);

        if ($rawContent === '') {
            $this->logger->warning('The math directive has no content. Did you properly indent the code? ', $directiveNode->getSourceLocation()->toLoggerInformation());

            return null;
        }

        return new MathNode(explode("\n", $rawContent));
    }
}
