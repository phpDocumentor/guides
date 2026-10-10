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

use phpDocumentor\Guides\Nodes\Inline\AbbreviationInlineNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\Validation\Violation;

use function preg_match;
use function trim;

/**
 * Role to create an abbreviation.
 *
 * Example:
 *
 * ```rest
 * :abbreviation:`term (some term definition)`
 * ```
 */
final class AbbreviationTextRole extends BaseTextRole
{
    protected string $name = 'abbreviation';

    /** @return string[] */
    public function getAliases(): array
    {
        return ['abbr'];
    }

    /** @return AbbreviationInlineNode */
    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): InlineNode {
        if (preg_match('/([^\(]+)\(([^\)]+)\)$/', $content, $matches) === 1) {
            return new AbbreviationInlineNode(trim($matches[1]), trim($matches[2]));
        }

        $documentParserContext->getViolationReporter()->report(Violation::warning(
            'rst.abbreviation.missing-definition',
            'Abbreviation has no definition. Usage: :abbreviation:`term (some term definition)`',
            $documentParserContext->getContext()->getLoggerInformation(),
        ));

        return new AbbreviationInlineNode($content, '');
    }
}
