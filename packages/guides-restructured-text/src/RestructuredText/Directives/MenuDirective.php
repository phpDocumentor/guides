<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;

use function count;

/**
 * A menu directives displays a menu in the page. In opposition to a toctree directive the menu
 * is for display only. It does not change the position of document in the document tree and can therefore be included
 * all pages as navigation.
 *
 * By default it displays a menu of the pages on level 1 up to level 2.
 */
class MenuDirective extends BaseDirective
{
    public function __construct(private readonly ToctreeBuilder $toctreeBuilder)
    {
    }

    public function getName(): string
    {
        return 'menu';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $parserContext = $blockContext->getDocumentParserContext()->getParser()->getParserContext();
        $options = $directive->getOptions();
        $options['glob'] = new DirectiveOption('glob', true);
        $options['titlesonly'] = new DirectiveOption('titlesonly', false);
        $options['globExclude'] ??= new DirectiveOption('globExclude', 'index,Index');

        $toctreeFiles = $this->toctreeBuilder->buildToctreeFiles(
            $parserContext,
            $blockContext->getDocumentIterator(),
            $options,
        );
        if (count($toctreeFiles) === 0) {
            $toctreeFiles[] = '/*';
        }

        return (new NavMenuNode($toctreeFiles))->withOptions($this->optionsToArray($options));
    }
}
