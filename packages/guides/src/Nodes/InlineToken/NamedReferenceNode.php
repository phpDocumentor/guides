<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

/**
 *
 */
class NamedReferenceNode extends AbstractLinkToken
{
    public const TYPE = 'named_reference';
    private string $url = '';

    public function __construct(
        private readonly string $id,
        private readonly string $referenceName,
        private readonly string|null $text = null,
    ) {
        parent::__construct(self::TYPE, $id, $referenceName);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReferenceName(): string
    {
        return $this->referenceName;
    }

    public function getText(string|null $default = null): string
    {
        return $this->text ?? $default ?? $this->referenceName;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
