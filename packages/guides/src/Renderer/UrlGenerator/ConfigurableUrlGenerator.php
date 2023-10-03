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

namespace phpDocumentor\Guides\Renderer\UrlGenerator;

use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Settings\SettingsManager;

final class ConfigurableUrlGenerator extends AbstractUrlGenerator
{
    public function __construct(
        private readonly SettingsManager $settingsManager,
        private readonly AbsoluteUrlGenerator $absoluteUrlGenerator,
        private readonly RelativeUrlGenerator $relativeUrlGenerator,
    ) {
    }

    public function createFileUrl(string $filename, string $outputFormat = 'html', string|null $anchor = null): string
    {
        return $filename . '.' . $outputFormat .
            ($anchor !== null ? '#' . $anchor : '');
    }

    public function generateInternalPathFromRelativeUrl(
        RenderContext $renderContext,
        string $canonicalUrl,
    ): string {
        if ($this->settingsManager->getProjectSettings()->isLinksRelative()) {
            return $this->relativeUrlGenerator->generateInternalPathFromRelativeUrl($renderContext, $canonicalUrl);
        }

        return $this->absoluteUrlGenerator->generateInternalPathFromRelativeUrl($renderContext, $canonicalUrl);
    }
}
