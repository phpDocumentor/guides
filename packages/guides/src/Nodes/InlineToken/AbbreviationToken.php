<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Nodes\InlineToken;

class AbbreviationToken extends GenericTextRoleToken
{
    public const TYPE = 'abbreviation';

    public function __construct(string $id, private readonly string $term, private readonly string $definition)
    {
        parent::__construct($id, self::TYPE, $term);
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function getDefinition(): string
    {
        return $this->definition;
    }
}
