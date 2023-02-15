<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

/**
 * @extends AbstractNode<string>
 * @deprecated Tie-ing nodes to templates should be done differently; as this creates coupling between the parsing and
 *   rendering phase.
 */
final class TemplatedNode extends AbstractNode
{
    /** @var array<string, mixed> */
    private array $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(string $value, array $data)
    {
        $this->value = $value;
        $this->data = $data;
    }

    /** @return array<string, mixed> */
    public function getData(): array
    {
        return $this->data;
    }
}
