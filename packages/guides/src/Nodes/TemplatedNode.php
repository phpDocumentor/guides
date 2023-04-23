<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

/**
 * @deprecated Tie-ing nodes to templates should be done differently; as this creates coupling between the parsing and
 *   rendering phase.
 *
 * @extends AbstractNode<string>
 */
final class TemplatedNode extends AbstractNode
{
    /** @param array<string, mixed> $data */
    public function __construct(string $value, private readonly array $data)
    {
        $this->value = $value;
    }

    /** @return array<string, mixed> */
    public function getData(): array
    {
        return $this->data;
    }
}
