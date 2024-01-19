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

use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use phpDocumentor\Guides\Settings\SettingsManager;

final class DocumentParserContextFactory
{
    public function __construct(
        private readonly TextRoleFactory $textRoleFactory,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function create(MarkupLanguageParser $markupLanguageParser): DocumentParserContext
    {
        $documentParser = new DocumentParserContext(
            $markupLanguageParser->getParserContext(),
            $this->textRoleFactory,
            $markupLanguageParser,
        );

        $documentParser->setCodeBlockDefaultLanguage($this->settingsManager->getProjectSettings()->getDefaultCodeLanguage());

        return $documentParser;
    }
}
