<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\Logger;

use PHPUnit\Framework\TestCase;

class SpyProcessorTest extends TestCase
{
    public function testHasBeenCalledReturnsFalseByDefault(): void
    {
        $spyProcessor = new SpyProcessor();

        self::assertFalse($spyProcessor->hasBeenCalled());
    }

    public function testItKnowsWhenALogIsEmitted(): void
    {
        $process = new SpyProcessor();
        $process(['channel' => 'test']);
        self::assertTrue($process->hasBeenCalled());
    }
}
