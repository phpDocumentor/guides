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

namespace phpDocumentor\Guides\ReferenceResolvers;

use function end;

final class Messages
{
    /** @var list<Message> */
    private array $warnings = [];

    public function addWarning(Message $warning): void
    {
        $this->warnings[] = $warning;
    }

    /** @return Message[] */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getLastWarning(): Message|null
    {
        if (!empty($this->warnings)) {
            return end($this->warnings);
        }

        return null;
    }
}
