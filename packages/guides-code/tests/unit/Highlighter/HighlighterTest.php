<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Code\Highlighter;

use Exception;
use Highlight\Highlighter as HighlightPHP;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;

class HighlighterTest extends TestCase
{
    public function testItEscapesCharactersForbiddenInPreTags(): void
    {
        $highlight = new Highlighter(new HighlightPHP(), new NullLogger());
        $result = $highlight('text', <<<'TXT'
        < I'm an expert in my field. >
          ---------------------------
              \   ^__^
               \  (oo)\_______
                  (__)\       )\/\
                      ||----w |
                      ||     ||
        TXT);
        self::assertSame(<<<'TXT'
        &lt; I'm an expert in my field. &gt;
          ---------------------------
              \   ^__^
               \  (oo)\_______
                  (__)\       )\/\
                      ||----w |
                      ||     ||
        TXT, $result->code, 'The greater than and less than signs should be escaped');
    }

    public function testItIsIdempotent(): void
    {
        $highlight = new Highlighter(new HighlightPHP(), new NullLogger());
        $result = $highlight('text', <<<'TXT'
        &lt; I'm an expert in my field. &gt;
          ---------------------------
              \   ^__^
               \  (oo)\_______
                  (__)\       )\/\
                      ||----w |
                      ||     ||
        TXT);
        self::assertSame(<<<'TXT'
        &lt; I'm an expert in my field. &gt;
          ---------------------------
              \   ^__^
               \  (oo)\_______
                  (__)\       )\/\
                      ||----w |
                      ||     ||
        TXT, $result->code, 'Nothing should change');
    }

    public function testItCatchesAndLogsExceptions(): void
    {
        $highlighter = $this->createStub(HighlightPHP::class);
        $logger = new TestLogger();
        $highlight = new Highlighter($highlighter, $logger);

        $highlighter->method('highlight')->willThrowException(new Exception('test'));
        $highlight('php', 'echo "Hello world";');

        self::assertTrue($logger->hasErrorRecords(), 'An error should be logged');
        self::assertTrue($logger->hasErrorThatPasses(static function (array $record): bool {
            return isset($record['context']['exception'], $record['context']['code'])
                && $record['context']['code'] === 'echo "Hello world";';
        }));
    }

    public function testItUnderstandsAliases(): void
    {
        $highlighter = $this->createMock(HighlightPHP::class);
        $highlight = new Highlighter(
            $highlighter,
            new NullLogger(),
            ['attribute' => 'php'],
        );

        $highlighter->expects(self::once())
            ->method('highlight')
            ->with('php', '#[Attribute]')
            ->willReturn((object) ['language' => 'php', 'value' => '<span class="hljs-attribute">#[Attribute]</span>']);

        $highlight('attribute', '#[Attribute]');
    }
}
