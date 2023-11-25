<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

use function array_merge;

/**
 * Represents a link to document
 *
 * Supported formats
 * :doc:`foo`
 * :doc:`domain:foo`
 * :doc:`foo/subdoc#anchor`
 * :doc:`custom text <foo>`
 * :doc:`custom text <domain:foo/subdoc#anchor>`
 */
class DocReferenceNode extends AbstractLinkInlineNode implements CrossReferenceNode
{
    final public const TYPE = 'doc';

    public function __construct(
        string $targetDocument,
        string $value = '',
        private readonly string $interlinkDomain = '',
    ) {
        parent::__construct(self::TYPE, $targetDocument, $value);
    }

    public function getInterlinkDomain(): string
    {
        return $this->interlinkDomain;
    }

    /** @return array<string, string> */
    public function getDebugInformation(): array
    {
        return array_merge(parent::getDebugInformation(), [
            'interlinkDomain' => $this->getInterlinkDomain(),
        ]);
    }
}
