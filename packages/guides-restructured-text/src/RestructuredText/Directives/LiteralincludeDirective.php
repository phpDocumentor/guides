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
use RuntimeException;

use function explode;
use function sprintf;

final class LiteralincludeDirective extends BaseDirective
{
    public function __construct(private readonly CodeNodeOptionMapper $codeNodeOptionMapper)
    {
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

        $codeNode = new CodeNode(explode("\n", $contents));
        $this->codeNodeOptionMapper->apply($codeNode, $directive->getOptions(), $blockContext);

        return $codeNode;
    }
}
