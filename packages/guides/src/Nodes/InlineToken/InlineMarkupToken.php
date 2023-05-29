<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

use phpDocumentor\Guides\Nodes\AbstractNode;

/** @extends AbstractNode<String> */
class InlineMarkupToken extends AbstractNode
{
    public const TYPE_REFERENCE = 'reference';
    public const TYPE_LINK = 'link';

    /** @param string[] $token */
    public function __construct(private readonly string $type, private readonly string $id, private array $token)
    {
        $this->token['type'] = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function get(string $key): string
    {
        return $this->token[$key] ?? '';
    }

    /** @return string[] */
    public function getTokenData(): array
    {
        return $this->token;
    }
}
