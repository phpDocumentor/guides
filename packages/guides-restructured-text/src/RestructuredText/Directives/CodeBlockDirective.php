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
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use Psr\Log\LoggerInterface;

use function trim;

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
final class CodeBlockDirective extends BaseDirective
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CodeNodeOptionMapper $codeNodeOptionMapper,
    ) {
    }

    public function getName(): string
    {
        return 'code-block';
    }

    /** {@inheritDoc} */
    public function getAliases(): array
    {
        return ['code', 'parsed-literal'];
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        if ($blockContext->getDocumentIterator()->isEmpty()) {
            $this->logger->warning('The code-block has no content. Did you properly indent the code? ', $blockContext->getLoggerInformation());

            return null;
        }

        $node = new CodeNode(
            $blockContext->getDocumentIterator()->toArray(),
        );

        if (trim($directive->getData()) !== '') {
            $node->setLanguage(trim($directive->getData()));
        } else {
            $node->setLanguage($blockContext->getDocumentParserContext()->getCodeBlockDefaultLanguage());
        }

        $this->codeNodeOptionMapper->apply($node, $directive->getOptions(), $blockContext);

        if ($directive->getVariable() !== '') {
            $document = $blockContext->getDocumentParserContext()->getDocument();
            $document->addVariable($directive->getVariable(), $node);

            return null;
        }

        return $node;
    }
}
