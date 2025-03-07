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
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\Interlink\InterlinkParser;

/**
 * Role to create a reference to a document.
 *
 * Example:
 *
 * ```rest
 * :doc:`doc/index`
 * ```
 */
final class DocReferenceTextRole extends AbstractReferenceTextRole
{
    final public const NAME = 'doc';

    public function __construct(
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

    /** @return DocReferenceNode */
    protected function createNode(string $referenceTarget, string|null $referenceName, string $role): AbstractLinkInlineNode
    {
        $interlinkData = $this->interlinkParser->extractInterlink($referenceTarget);

        return new DocReferenceNode($interlinkData->reference, $referenceName ? [new PlainTextInlineNode($referenceName)] : [], $interlinkData->interlink);
    }
}
