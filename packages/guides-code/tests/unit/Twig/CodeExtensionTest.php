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

namespace phpDocumentor\Guides\Code\Twig;

use Highlight\Highlighter as HighlightPHP;
use phpDocumentor\Guides\Code\Highlighter\HighlightPhpHighlighter;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class CodeExtensionTest extends TestCase
{
    private CodeExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new CodeExtension(new HighlightPhpHighlighter(new HighlightPHP(), new NullLogger()));
    }

    public function testItHighlightsCodeWithoutALanguageAsPlainText(): void
    {
        // A highlighter treats this differently per language: as "text" the quoted part stays plain, in a
        // programming language it becomes a string token. Plain input would be returned unchanged by every
        // language and could therefore not tell the fallback apart from any other one.
        $code = '<a> & "b"';

        self::assertSame(
            $this->extension->highlight([], $code, 'text'),
            $this->extension->highlight([], $code, null),
            'A CodeNode without a language must be rendered like an explicit "text" language',
        );

        self::assertNotSame(
            $this->extension->highlight([], $code, 'php'),
            $this->extension->highlight([], $code, null),
            'The fixture must be able to tell the "text" fallback apart from another language',
        );
    }

    public function testItHighlightsCodeWithALanguage(): void
    {
        self::assertStringContainsString(
            'hljs-keyword',
            $this->extension->highlight([], '<?php return 1;', 'php'),
        );
    }
}
