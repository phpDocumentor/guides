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

use phpDocumentor\Guides\Nodes\TextDirection;

final class LanguageInlineNode extends GenericTextRoleInlineNode
{
    public const TYPE = 'lang';

    public function __construct(
        private readonly string|null $language,
        private readonly TextDirection|null $direction,
        string $text,
        string $class = '',
    ) {
        parent::__construct(self::TYPE, $text, $class);
    }

    public function getLanguage(): string|null
    {
        return $this->language;
    }

    public function getDirection(): TextDirection|null
    {
        return $this->direction;
    }
}
