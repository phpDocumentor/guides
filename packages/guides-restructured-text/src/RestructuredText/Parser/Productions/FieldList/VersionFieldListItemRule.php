<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use Psr\Log\LoggerInterface;

use function sprintf;
use function strtolower;

class VersionFieldListItemRule implements FieldListItemRule
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'version';
    }

    public function apply(FieldListItemNode $fieldListItemNode, DocumentParserContext $documentParserContext): MetadataNode|null
    {
        $currentVersion = $documentParserContext->getProjectNode()->getVersion();
        if (
            $currentVersion !== null
            && $currentVersion !== $fieldListItemNode->getPlaintextContent()
        ) {
            $this->logger->warning(sprintf(
                'Project version was set more then once: %s and %s',
                $currentVersion,
                $fieldListItemNode->getPlaintextContent(),
            ));
            return null;
        }

        $documentParserContext->getProjectNode()->setVersion($fieldListItemNode->getPlaintextContent());

        return null;
    }
}
