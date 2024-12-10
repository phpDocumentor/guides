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

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\RestructuredText\Parser\Interlink\InterlinkParser;

final class ApiClassTextRole extends AbstractReferenceTextRole
{
    final public const NAME = 'api-class';
    final public const TYPE = 'api-class';
    protected bool $useRawContent = true;

    public function __construct(
        private readonly GenericLinkProvider $genericLinkProvider,
        private readonly AnchorNormalizer $anchorReducer,
        private readonly InterlinkParser $interlinkParser,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /** @inheritDoc */
    public function getAliases(): array
    {
        return [];
    }

    /** @return ReferenceNode */
    protected function createNode(string $referenceTarget, string|null $referenceName, string $role): AbstractLinkInlineNode
    {
        $interlinkData = $this->interlinkParser->extractInterlink($referenceTarget);
        $reference = $this->anchorReducer->reduceAnchor($interlinkData->reference);
        $prefix = $this->genericLinkProvider->getLinkPrefix($role);

        return new ReferenceNode($reference, $referenceName ? [new PlainTextInlineNode($referenceName)] : [], $interlinkData->interlink, self::TYPE, $prefix);
    }
}
