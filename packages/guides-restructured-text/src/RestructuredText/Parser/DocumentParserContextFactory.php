<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use phpDocumentor\Guides\Settings\SettingsManager;

class DocumentParserContextFactory
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
