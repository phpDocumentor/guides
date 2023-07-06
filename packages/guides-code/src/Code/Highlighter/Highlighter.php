<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Code\Highlighter;

use Highlight\Highlighter as HighlightPHP;
use Psr\Log\LoggerInterface;
use Throwable;

use function assert;
use function is_string;
use function preg_replace;
use function strtr;

/**
 * This class delegates to the Highlighter library the task of highlighting
 * code blocks, adds extra features, and makes sure that errors result in a log
 * line instead of an exception.
 */
final class Highlighter
{
    /**
     * @param array<string,string> $languageAliases a map of language aliases
     *                                             to their actual language,
     *                                             for instance "attribute" => "php"
     */
    public function __construct(
        private HighlightPHP $highlighter,
        private LoggerInterface $logger,
        private array $languageAliases = ['terminal' => 'bash'],
    ) {
    }

    public function __invoke(string $language, string $code): HighlightResult
    {
        if ($language === 'text') {
            // Highlighter escapes correctly the code, we need to manually escape only for "text" code
            $code = $this->escapeForbiddenCharactersInsideCodeBlock($code);

            return new HighlightResult('text', $code);
        }

        try {
            $highlight = $this->highlighter->highlight($this->languageAliases[$language] ?? $language, $code);

            $highlightValue = $highlight->value;
            $highlightLanguage = $highlight->language;

            if ($language === 'terminal') {
                $highlightValue = preg_replace('/^\$ /m', '<span class="hljs-prompt">$ </span>', $highlightValue);
                assert(is_string($highlightValue));
                $highlightValue = preg_replace('/^C:\\\&gt; /m', '<span class="hljs-prompt">C:\&gt; </span>', $highlightValue);
                assert(is_string($highlightValue));
            }

            return new HighlightResult($highlightLanguage, $highlightValue);
        } catch (Throwable $e) {
            $this->logger->error(
                <<<'MESSAGE'
                Error highlighting {language} code block!

                Code:

                ```
                {code}
                ```

                {exception}
                MESSAGE,
                [
                    'language' => $language,
                    'code' => $code,
                    'exception' => $e,
                ],
            );

            return new HighlightResult($language, $code);
        }
    }

    /**
     * Code blocks are displayed in "<pre>" tags, which has some reserved characters:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Element/pre
     */
    private function escapeForbiddenCharactersInsideCodeBlock(string $code): string
    {
        $codeEscaped = preg_replace('/&(?!amp;|lt;|gt;|quot;)/', '&amp;', $code);

        assert(is_string($codeEscaped));

        return strtr($codeEscaped, ['<' => '&lt;', '>' => '&gt;', '"' => '&quot;']);
    }
}
