<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Code\Highlighter;

use Highlight\Highlighter as HighlightPHP;
use Psr\Log\LoggerInterface;
use Throwable;

use function array_merge;
use function assert;
use function is_string;
use function preg_replace;
use function strtr;

/**
 * This class delegates to the Highlighter library the task of highlighting
 * code blocks, adds extra features, and makes sure that errors result in a log
 * line instead of an exception.
 */
final class HighlightPhpHighlighter implements Highlighter
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

    /** @param $debugInformation array<string, string|null> */
    public function __invoke(string $language, string $code, array $debugInformation): HighlightResult
    {
        if ($language === 'text' || $language === '' || $language === 'rest') {
            // Highlighter escapes correctly the code, we need to manually escape only for "text" code
            $code = $this->escapeForbiddenCharactersInsideCodeBlock($code);

            return new HighlightResult('text', $code);
        }

        try {
            $highlight = $this->highlighter->highlight($this->languageAliases[$language] ?? $language, $code);

            $highlightValue = $highlight->value;
            $highlightLanguage = $highlight->language;

            return new HighlightResult($highlightLanguage, $highlightValue);
        } catch (Throwable $e) {
            $this->logger->warning(
                <<<'MESSAGE'
                Highlighting {language} code-block failed

                Code:

                ```
                {code}
                ```

                {exception}
                MESSAGE,
                array_merge($debugInformation, [
                    'language' => $language,
                    'code' => $code,
                    'exception' => $e,
                ]),
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
