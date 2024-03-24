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

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
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
        private readonly AnchorNormalizer $anchorReducer,
        private readonly InlineParser $inlineParser,
    ) {
        parent::__construct($startingRule);

        $genericLinkProvider->addGenericLink(self::NAME, ConfvalNode::LINK_TYPE, ConfvalNode::LINK_PREFIX);
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
        $id = $directive->getData();
        if ($directive->hasOption('name')) {
            $id = (string) $directive->getOption('name')->getValue();
        }

        $id = $this->anchorReducer->reduceAnchor($id);
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

        $noindex = $directive->hasOption('noindex');

        foreach ($directive->getOptions() as $option) {
            if (in_array($option->getName(), ['type', 'required', 'default', 'noindex', 'name'], true)) {
                continue;
            }

            $additionalOptions[$option->getName()] = $this->inlineParser->parse($option->toString(), $blockContext);
        }

        return new ConfvalNode($id, $directive->getData(), $type, $required, $default, $additionalOptions, $collectionNode->getChildren(), $noindex);
    }
}
