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
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use Psr\Log\LoggerInterface;

use function sprintf;
use function strtolower;

final class ProjectFieldListItemRule implements FieldListItemRule
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'project';
    }

    public function apply(FieldListItemNode $fieldListItemNode, BlockContext $blockContext): MetadataNode|null
    {
        $currentTitle = $blockContext->getDocumentParserContext()->getProjectNode()->getTitle();
        $newTitle = $fieldListItemNode->getPlaintextContent();
        if (
            $currentTitle !== null
            && $currentTitle !== $newTitle
        ) {
            $this->logger->warning(sprintf(
                'Project title was set more then once: %s and %s',
                $currentTitle,
                $newTitle,
            ), $blockContext->getLoggerInformation());
        }

        $blockContext->getDocumentParserContext()->getProjectNode()->setTitle($newTitle);

        return null;
    }
}
