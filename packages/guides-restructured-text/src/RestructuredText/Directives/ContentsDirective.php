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

use phpDocumentor\Guides\Nodes\Menu\ContentMenuNode;
use phpDocumentor\Guides\Nodes\Menu\SectionMenuEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * Standarad rst `contents` directive
 *
 * Displays a table of content of the current page
 */
#[Option(name: 'local', type: OptionType::Boolean, description: 'If set, the table of contents will only include sections that are local to the current document.', default: false)]
#[Option(name: 'depth', description: 'The maximum depth of the table of contents.')]
final class ContentsDirective extends BaseDirective
{
    public function __construct(
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
    }

    public function getName(): string
    {
        return 'contents';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $options = $directive->getOptions();
        $absoluteUrl = $this->documentNameResolver->absoluteUrl(
            $blockContext->getDocumentParserContext()->getContext()->getDirName(),
            $blockContext->getDocumentParserContext()->getContext()->getCurrentFileName(),
        );

        return (new ContentMenuNode([new SectionMenuEntryNode($absoluteUrl)]))
            ->withOptions($this->optionsToArray($options))
            ->withCaption($directive->getDataNode())
            ->withLocal($this->readOption($directive, 'local'));
    }
}
