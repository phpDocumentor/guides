<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\NodeRenderers\Html\GeneralNodeHtmlRenderer;
use phpDocumentor\Guides\NodeRenderers\LaTeX\GeneralNodeLatexRenderer;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\AnchorReducer;
use phpDocumentor\Guides\RestructuredText\Nodes\ConfvalNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\InlineParser;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericLinkProvider;

use function boolval;
use function in_array;

/**
 * The confval directive configuration values.
 *
 * https://sphinx-toolbox.readthedocs.io/en/stable/extensions/confval.html
 */
final class ConfvalDirective extends SubDirective
{
    public const NAME = 'confval';

    /** @param Rule<CollectionNode> $startingRule */
    public function __construct(
        protected Rule $startingRule,
        GenericLinkProvider $genericLinkProvider,
        GeneralNodeHtmlRenderer $generalNodeRenderer,
        GeneralNodeLatexRenderer $generalNodeLatexRenderer,
        private readonly AnchorReducer $anchorReducer,
        private readonly InlineParser $inlineParser,
    ) {
        parent::__construct($startingRule);

        $generalNodeRenderer->registerNode(ConfvalNode::class, 'body/directive/confval.html.twig');
        $generalNodeLatexRenderer->registerNode(ConfvalNode::class, 'body/directive/confval.tex.twig');
        $genericLinkProvider->addGenericLink(self::NAME, ConfvalNode::LINK_TYPE);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $id = $this->anchorReducer->reduceAnchor($directive->getData());
        $type = null;
        $required = false;
        $default = null;
        $additionalOptions = [];
        if ($directive->hasOption('type')) {
            $type = $this->inlineParser->parse($directive->getOption('type')->toString(), $blockContext);
        }

        if ($directive->hasOption('required')) {
            $required = boolval($directive->getOption('required'));
        }

        if ($directive->hasOption('default')) {
            $type = $this->inlineParser->parse($directive->getOption('default')->toString(), $blockContext);
        }

        foreach ($directive->getOptions() as $option) {
            if (in_array($option->getName(), ['type', 'required', 'default', 'noindex'], true)) {
                continue;
            }

            $additionalOptions[$option->getName()] = $this->inlineParser->parse($option->toString(), $blockContext);
        }

        return new ConfvalNode($id, $directive->getData(), $type, $required, $default, $additionalOptions, $collectionNode->getChildren());
    }
}
