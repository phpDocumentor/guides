<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes;

use phpDocumentor\Guides\Nodes\InlineToken\PlainTextToken;

use function date;

use const DATE_RFC2822;

/** @extends CompoundNode<DocumentNode> */
class ProjectNode extends CompoundNode
{
    /**
     * Variables are replacements in a document or project.
     *
     * Variables like |project| and |version| are replaced globally
     *
     * @var array<Node>
     */
    private array $variables = [];

    public function __construct(
        private string|null $title = null,
        private string|null $version = null,
    ) {
        $this->addVariable('project', new PlainTextToken($title ?? ''));
        $this->addVariable('version', new PlainTextToken($version ?? ''));
        $this->addVariable('last_rendered', new PlainTextToken(date(DATE_RFC2822)));

        parent::__construct();
    }

    public function getVersion(): string|null
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getVariable(string $name, Node|null $default): Node|null
    {
        return $this->variables[$name] ?? $default;
    }

    public function addVariable(string $name, Node $value): void
    {
        $this->variables[$name] = $value;
    }
}
