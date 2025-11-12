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

namespace phpDocumentor\Guides\RestructuredText\Parser;

use Doctrine\Common\Lexer\AbstractLexer;
use phpDocumentor\Guides\ReferenceResolvers\ExternalReferenceResolver;
use ReflectionClass;

use function array_column;
use function array_flip;
use function ctype_alnum;
use function ctype_space;
use function parse_url;
use function preg_match;
use function str_ends_with;
use function str_replace;
use function strlen;
use function substr;

use const PHP_URL_SCHEME;
use const PHP_VERSION_ID;

/** @extends AbstractLexer<int, string> */
final class InlineLexer extends AbstractLexer
{
    public const WORD = 1;
    public const UNDERSCORE = 2;
    public const ANONYMOUS_END = 3;
    public const LITERAL = 5;
    public const BACKTICK = 6;
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
    public const VARIABLE_DELIMITER = 24;
    public const ESCAPED_SIGN = 25;

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
            '\\\\``', // must be a separate case, as the next pattern would split in "\`" + "`", causing it to become a intepreted text
            '\\\\[\s\S]', // Escaping hell... needs escaped slash in regex, but also in php.
            '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}',
            '(?<=^|\s)[a-z0-9-]+_{2}', //Inline href.
            '(?<=^|\s)[a-z0-9-]+_{1}(?=[\s\.+]|$)', //Inline href.
            '``.+?``(?!`)',
            '_{2}',
            '_',
            '`',
            ':',
            '|',
            '\\*\\*',
            '\\*',
            '\b(?<!:)[a-z0-9\\.\-+]{2,}:\\/\\/[-a-zA-Z0-9@:%_\\+.~#?&\\/=]*[-a-zA-Z0-9@%_\\+~#&\\/=]', // standalone hyperlinks
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
        //phpcs:ignore SlevomatCodingStandard.Numbers.RequireNumericLiteralSeparator.RequiredNumericLiteralSeparator
        if (PHP_VERSION_ID < 80500) {
            $property->setAccessible(true);
        }

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
    protected function getType(string &$value)
    {
        $type = match ($value) {
            '`' => self::BACKTICK,
            '``' => self::DOUBLE_BACKTICK,
            '**' => self::STRONG_DELIMITER,
            '*' => self::EMPHASIS_DELIMITER,
            '|' => self::VARIABLE_DELIMITER,
            '_' => self::UNDERSCORE,
            '__' => self::ANONYMOUS_END,
            ':' => self::COLON,
            '#' => self::OCTOTHORPE,
            '[' => self::ANNOTATION_START,
            ']' => self::ANNOTATION_END,
            '~' => self::NBSP,
            '\\``' => self::ESCAPED_SIGN,
            default => null,
        };

        if ($type !== null) {
            return $type;
        }

        // $value is already a tokenized part. Therefore, we have to match against the complete String here.
        if (str_ends_with($value, '__') && ctype_alnum(str_replace('-', '', substr($value, 0, -2)))) {
            return self::ANONYMOUSE_REFERENCE;
        }

        if (str_ends_with($value, '_') && ctype_alnum(str_replace('-', '', substr($value, 0, -1)))) {
            return self::NAMED_REFERENCE;
        }

        if (strlen($value) === 2 && $value[0] === '\\') {
            return self::ESCAPED_SIGN;
        }

        if (strlen($value) === 1 && ctype_space($value)) {
            return self::WHITESPACE;
        }

        if (preg_match('/^``.+``(?!`)$/i', $value)) {
            return self::LITERAL;
        }

        if (preg_match('/' . ExternalReferenceResolver::SUPPORTED_SCHEMAS . ':[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*[-a-zA-Z0-9()@%_\\+~#&\\/=]/', $value) && parse_url($value, PHP_URL_SCHEME) !== null) {
            return self::HYPERLINK;
        }

        if (preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/i', $value)) {
            return self::EMAIL;
        }

        return self::WORD;
    }
}
