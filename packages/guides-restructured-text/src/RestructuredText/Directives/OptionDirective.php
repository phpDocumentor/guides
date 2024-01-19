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
use phpDocumentor\Guides\RestructuredText\Nodes\OptionNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericLinkProvider;

use function array_map;
use function explode;
use function preg_replace;
use function str_contains;

/**
 * Describes a command line argument or switch. Option argument names should be enclosed in angle brackets.
 *
 * https://www.sphinx-doc.org/en/master/usage/restructuredtext/domains.html#directive-option
 */
final class OptionDirective extends SubDirective
{
    public const NAME = 'option';

    /** @param Rule<CollectionNode> $startingRule */
    public function __construct(
        protected Rule $startingRule,
        GenericLinkProvider $genericLinkProvider,
        private readonly AnchorNormalizer $anchorReducer,
    ) {
        parent::__construct($startingRule);

        $genericLinkProvider->addGenericLink(self::NAME, OptionNode::LINK_TYPE);
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
        $additionalIds = $this->getAdditionalIds($directive);

        $id = $this->anchorReducer->reduceAnchor($directive->getData());

        return new OptionNode($id, $directive->getData(), $additionalIds, $collectionNode->getChildren());
    }

    /** @return string[] */
    private function getAdditionalIds(Directive $directive): array
    {
        $additionalIds = [];
        if (str_contains($directive->getData(), ',')) {
            $additionalIds = explode(',', $directive->getData());
            $additionalIds = array_map('trim', $additionalIds);
            $additionalIds = array_map(function ($item) {
                // remove additional information in brackets like <module>
                $pattern = '/<([^>]+)>/';
                $item = preg_replace($pattern, '', $item);

                // only keep allowed signs
                return $this->anchorReducer->reduceAnchor($item ?? '');
            }, $additionalIds);
        }

        return $additionalIds;
    }
}
