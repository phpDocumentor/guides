<?php

declare(strict_types=1);

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
