<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Span;

use Doctrine\Common\Lexer\AbstractLexer;
use ReflectionClass;

use function array_column;
use function array_flip;
use function preg_match;

/** @extends AbstractLexer<int, string> */
final class SpanLexer extends AbstractLexer
{
    public const WORD = 1;
    public const UNDERSCORE = 2;
    public const ANONYMOUS_END = 3;
    public const PHRASE_ANONYMOUS_END = 4;
    public const LITERAL = 5;
    public const BACKTICK = 6;
    public const NAMED_REFERENCE_END = 7;
    public const INTERNAL_REFERENCE_START = 8;
    public const EMBEDED_URL_START = 9;
    public const EMBEDED_URL_END = 10;
    public const NAMED_REFERENCE = 11;
    public const ANONYMOUSE_REFERENCE = 12;
    public const COLON = 13;
    public const OCTOTHORPE = 14;
    public const WHITESPACE = 15;
    public const ANNOTATION_START = 16;
    public const ANNOTATION_END = 17;
    public const DOUBLE_BACKTICK = 18;
    public const HYPERLINK = 19;
    public const EMAIL = 20;
    public const EMPHASIS_DELIMITER = 21;
    public const STRONG_DELIMITER = 22;
    public const NBSP = 23;

    /**
     * Map between string position and position in token list.
     *
     * @link https://github.com/doctrine/lexer/issues/53
     *
     * @var array<int, int>
     */
    private array $tokenPositions = [];

    /** @return string[] */
    protected function getCatchablePatterns(): array
    {
        return [
            'https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)',
            '\\S+@\\S+\\.\\S+',
            '[a-z0-9-]+_{2}', //Inline href.
            '[a-z0-9-]+_{1}(?=[\s\.+]|$)', //Inline href.
            '``',
            '`__',
            '`_',
            '`~',
            '<',
            '>',
            '\\\\_', // Escaping hell... needs escaped slash in regex, but also in php.
            '_`',
            '`',
            '_{2}',
            ':',
            '\\*\\*',
            '\\*',
        ];
    }

    /** @param int $position */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function resetPosition($position = 0): void
    {
        parent::resetPosition($this->tokenPositions[$position]);
    }

    /** @param string $input */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    protected function scan($input): void
    {
        parent::scan($input);

        $class = new ReflectionClass(AbstractLexer::class);
        $property = $class->getProperty('tokens');
        $property->setAccessible(true);
        /** @var array<int, string> $tokens */
        $tokens = $property->getValue($this);

        $this->tokenPositions = array_flip(array_column($tokens, 'position'));
    }

    /** @return string[] */
    protected function getNonCatchablePatterns(): array
    {
        return [];
    }

    /** @inheritDoc */
    protected function getType(&$value)
    {
        if (preg_match('/https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)/i', $value)) {
            return self::HYPERLINK;
        }

        if (preg_match('/\\S+@\\S+\\.\\S+/i', $value)) {
            return self::EMAIL;
        }

        if (preg_match('/[a-z0-9-]+_{2}/i', $value)) {
            return self::ANONYMOUSE_REFERENCE;
        }

        if (preg_match('/[a-z0-9-]+_{1}/i', $value)) {
            return self::NAMED_REFERENCE;
        }

        if (preg_match('/\s/i', $value)) {
            return self::WHITESPACE;
        }

        switch ($value) {
            case '``':
                return self::DOUBLE_BACKTICK;

            case '`':
                return self::BACKTICK;

            case '**':
                return self::STRONG_DELIMITER;

            case '*':
                return self::EMPHASIS_DELIMITER;

            case '\_':
                $value = '_';
                break;
            case '<':
                return self::EMBEDED_URL_START;

            case '>':
                return self::EMBEDED_URL_END;

            case '_':
                return self::UNDERSCORE;

            case '`_':
                return self::NAMED_REFERENCE_END;

            case '_`':
                return self::INTERNAL_REFERENCE_START;

            case '__':
                return self::ANONYMOUS_END;

            case '`__':
                return self::PHRASE_ANONYMOUS_END;

            case ':':
                return self::COLON;

            case '#':
                return self::OCTOTHORPE;

            case '[':
                return self::ANNOTATION_START;

            case ']':
                return self::ANNOTATION_END;

            case '~':
                return self::NBSP;

            default:
                return self::WORD;
        }

        return null;
    }
}
