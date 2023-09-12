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
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\LiteralBlockNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DocumentRule;
use RuntimeException;

use function array_key_exists;
use function explode;
use function sprintf;
use function str_replace;

final class IncludeDirective extends BaseDirective
{
    public function __construct(private readonly DocumentRule $startingRule)
    {
    }

    public function getName(): string
    {
        return 'include';
    }

    /** {@inheritDoc} */
    public function processNode(
        BlockContext $blockContext,
        Directive $directive,
    ): Node {
        $parserContext = $blockContext->getDocumentParserContext()->getParser()->getParserContext();
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

        if (array_key_exists('literal', $directive->getOptions())) {
            $contents = str_replace("\r\n", "\n", $contents);

            return new LiteralBlockNode($contents);
        }

        if (array_key_exists('code', $directive->getOptions())) {
            $contents = str_replace("\r\n", "\n", $contents);
            $codeNode = new CodeNode(
                explode('\n', $contents),
            );
            $codeNode->setLanguage((string) $directive->getOption('code')->getValue());

            return $codeNode;
        }

        $currentDocument = $blockContext->getDocumentParserContext()->getDocument();
        $subContext = new BlockContext($blockContext->getDocumentParserContext(), $contents);
        $document = $this->startingRule->apply($subContext);

        //Reset the document, as it was changed by the apply method.
        $blockContext->getDocumentParserContext()->setDocument($currentDocument);

        return new CollectionNode($document->getChildren());
    }
}
