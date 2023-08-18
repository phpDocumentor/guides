<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;

/**
 * Sphinx based Toctree directive.
 *
 * This directive has an issue, as the related documents are resolved on parse, but during the rendering
 * we are using the {@see Metas} to collect the titles of those documents. There is some step missing in our process
 * which could be resolved by using https://github.com/phpDocumentor/guides/pull/21?
 *
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#table-of-contents
 */
class ToctreeDirective extends BaseDirective
{
    public function __construct(private readonly ToctreeBuilder $toctreeBuilder)
    {
    }

    public function getName(): string
    {
        return 'toctree';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $parserContext = $blockContext->getDocumentParserContext()->getParser()->getParserContext();
        $options = $directive->getOptions();
        $options['globExclude'] ??= new DirectiveOption('globExclude', 'index,Index');

        $toctreeFiles = $this->toctreeBuilder->buildToctreeFiles(
            $parserContext,
            $blockContext->getDocumentIterator(),
            $options,
        );

        return (new TocNode($toctreeFiles))->withOptions($this->optionsToArray($options));
    }
}
