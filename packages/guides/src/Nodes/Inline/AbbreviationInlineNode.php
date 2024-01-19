<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Nodes\Inline;

final class AbbreviationInlineNode extends GenericTextRoleInlineNode
{
    public const TYPE = 'abbreviation';

    public function __construct(private readonly string $term, private readonly string $definition)
    {
        parent::__construct(self::TYPE, $term);
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
