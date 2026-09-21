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

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\Inline\LanguageInlineNode;
use phpDocumentor\Guides\Nodes\Language;
use phpDocumentor\Guides\Nodes\TextDirection;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use Psr\Log\LoggerInterface;

use function explode;
use function preg_match;
use function sprintf;
use function trim;

/**
 * Role to mark text as being in another language and, optionally, another text direction.
 *
 * Example:
 *
 * ```rest
 * :lang:`مثال عربي (ar)`
 * :lang:`مثال عربي (ar, rtl)`
 * ```
 */
final class LangTextRole extends BaseTextRole
{
    protected string $name = 'lang';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): InlineNode {
        if (preg_match('/([^\(]+)\(([^\)]+)\)$/', $content, $matches) !== 1) {
            $this->logger->warning(
                'The "lang" role requires a language. Usage: :lang:`text (language)` or :lang:`text (language, direction)`',
                $documentParserContext->getContext()->getLoggerInformation(),
            );

            return new LanguageInlineNode(null, null, $content, $this->getClass());
        }

        $text = trim($matches[1]);
        $parts = explode(',', $matches[2]);
        $language = trim($parts[0]);
        $written = isset($parts[1]) ? trim($parts[1]) : null;

        if (preg_match(Language::PATTERN, $language) !== 1) {
            $this->logger->warning(
                sprintf(
                    'The "lang" role expects a BCP 47 language tag (e.g. "en", "en-US"), but was given "%s".',
                    $language,
                ),
                $documentParserContext->getContext()->getLoggerInformation(),
            );
        }

        $direction = $written === null ? null : TextDirection::tryFromUserInput($written);
        if ($written !== null && $direction === null) {
            $this->logger->warning(
                sprintf(
                    'The "lang" role expects the direction to be one of "ltr", "rtl" or "auto", but was given "%s".',
                    $written,
                ),
                $documentParserContext->getContext()->getLoggerInformation(),
            );

            $direction = TextDirection::Auto;
        }

        return new LanguageInlineNode($language, $direction, $text, $this->getClass());
    }
}
