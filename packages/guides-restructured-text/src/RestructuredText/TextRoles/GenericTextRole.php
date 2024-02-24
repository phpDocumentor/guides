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

use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleInlineNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\Settings\SettingsManager;

use function explode;
use function in_array;
use function str_contains;

final class GenericTextRole extends BaseTextRole
{
    protected string $name = 'default';
    protected string|null $baseRole = null;

    public function __construct(
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function processNode(
        DocumentParserContext $documentParserContext,
        string $role,
        string $content,
        string $rawContent,
    ): InlineNode {
        if (str_contains($role, ':')) {
            [$domainName, $directiveName] = explode(':', $role);
            if (in_array($domainName, $this->settingsManager->getProjectSettings()->getIgnoredDomains(), true)) {
                return new PlainTextInlineNode($content);
            }
        }

        return new GenericTextRoleInlineNode($this->baseRole ?? $role, $content, $this->getClass());
    }

    public function getBaseRole(): string|null
    {
        return $this->baseRole;
    }

    public function setBaseRole(string|null $baseRole): void
    {
        $this->baseRole = $baseRole;
    }
}
