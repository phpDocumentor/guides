<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
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

    public function apply(FieldListItemNode $fieldListItemNode, DocumentNode $documentNode): MetadataNode|null
    {
        $currentVersion = $documentNode->getProjectNode()->getVersion();
        if (
            $currentVersion !== null
            && $currentVersion !== $fieldListItemNode->getPlaintextContent()
        ) {
            $this->logger->warning(sprintf(
                'Project version was set more then once: %s and %s',
                $currentVersion,
                $fieldListItemNode->getPlaintextContent(),
            ));
        }

        $documentNode->getProjectNode()->setVersion($fieldListItemNode->getPlaintextContent());

        return null;
    }
}
