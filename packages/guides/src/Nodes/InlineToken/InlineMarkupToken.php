<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

use phpDocumentor\Guides\Nodes\AbstractNode;

/** @extends AbstractNode<String> */
abstract class InlineMarkupToken extends AbstractNode
{
    /** @param string[] $token */
    public function __construct(private readonly string $type, private readonly string $id, string $value = '', private array $token = [])
    {
        $this->value = $value;
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
