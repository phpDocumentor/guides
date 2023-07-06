<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\Inline;

use phpDocumentor\Guides\Meta\Target;

/**
 * This class should be moved into Nodes, but right now the span parser is producing this.
 * I just want to get started to improve reference handling
 *
 * CrossReferences are references outside a document. As parsing is file based normal references are in document,
 * refering to other documents.
 *
 * Supported formats
 * :ref:`link`
 * :ref:`domain:link`
 * :ref:`custom text <link>`
 * :ref:`custom text <domain:link>`
 *
 * Cross references are resolved during rendering? -> Should be compiler.
 */
class ReferenceNode extends AbstractLinkInlineNode
{
    public const TYPE = 'ref';
    // URL can only be resolved during rendering as it contains file endings for html / latex etc
    private string $url = '';
    // Is resolved in the compiler
    private Target|null $target = null;

    public function __construct(
        string $referenceName,
        private readonly string|null $domain = null,
        private readonly string|null $text = null,
    ) {
        parent::__construct(self::TYPE, $referenceName);
    }

    public function getReferenceName(): string
    {
        return $this->value;
    }

    public function getDomain(): string|null
    {
        return $this->domain;
    }

    public function getText(string|null $default = null): string
    {
        return $this->text ?? $this->target?->getTitle() ?? $this->value;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getTarget(): Target|null
    {
        return $this->target;
    }

    public function setTarget(Target $target): void
    {
        $this->target = $target;
    }
}
