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
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use PHPUnit\Framework\TestCase;

final class GenericTextRoleTest extends TestCase
{
    private SettingsManager $settingsManager;
    private ProjectSettings $projectSettings;
    private GenericTextRole $subject;
    private DocumentParserContext $documentParserContext;

    public function setUp(): void
    {
        $this->projectSettings = new ProjectSettings();
        $this->settingsManager = new SettingsManager($this->projectSettings);
        $this->documentParserContext = self::createMock(DocumentParserContext::class);
        $this->subject = new GenericTextRole(
            $this->settingsManager,
        );
    }

    public function testIgnoredDomainReturnsPlainTextInlineNode(): void
    {
        $this->projectSettings->setIgnoredDomains(['ignored', 'alsoignored']);
        $inline = $this->subject->processNode($this->documentParserContext, 'ignored:role', '', '');
        self::assertInstanceOf(PlainTextInlineNode::class, $inline);
    }

    public function testUnknownDomainReturnsGenericTextRoleInlineNode(): void
    {
        $this->projectSettings->setIgnoredDomains(['ignored', 'alsoignored']);
        $inline = $this->subject->processNode($this->documentParserContext, 'notignored:role', '', '');
        self::assertInstanceOf(GenericTextRoleInlineNode::class, $inline);
    }
}
