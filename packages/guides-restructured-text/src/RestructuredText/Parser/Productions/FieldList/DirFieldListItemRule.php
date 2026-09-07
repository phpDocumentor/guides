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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\DirectionNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use Psr\Log\LoggerInterface;

use function in_array;
use function sprintf;
use function strtolower;

final class DirFieldListItemRule implements FieldListItemRule
{
    private const VALID_DIRECTIONS = ['ltr', 'rtl', 'auto'];

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'dir';
    }

    public function apply(FieldListItemNode $fieldListItemNode, BlockContext $blockContext): MetadataNode
    {
        $direction = $fieldListItemNode->getPlaintextContent();
        if (!in_array($direction, self::VALID_DIRECTIONS, true)) {
            $this->logger->warning(
                sprintf(
                    'The "dir" field expects one of "ltr", "rtl" or "auto", but was given "%s".',
                    $direction,
                ),
                $blockContext->getLoggerInformation(),
            );
        }

        return new DirectionNode($direction);
    }
}
