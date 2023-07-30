<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

abstract class BaseTextRole implements TextRole
{
    protected string $name;
    protected string $class = '';

    public function getName(): string
    {
        return $this->name;
    }

    /** @return string[] */
    public function getAliases(): array
    {
        return [];
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function withName(string $name): BaseTextRole
    {
        $role = clone $this;
        $role->name = $name;

        return $role;
    }
}
