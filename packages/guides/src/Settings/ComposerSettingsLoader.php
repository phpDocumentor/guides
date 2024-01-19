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

namespace phpDocumentor\Guides\Settings;

use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\ProjectNode;

use function file_get_contents;
use function implode;
use function is_array;
use function is_string;
use function json_decode;
use function sprintf;

final class ComposerSettingsLoader
{
    public function loadSettings(ProjectNode $projectNode, ProjectSettings $projectSettings, string $pathToComposerJson): void
    {
        $jsonContents = file_get_contents($pathToComposerJson);
        if (!is_string($jsonContents)) {
            return;
        }

        $composerData = json_decode($jsonContents, true);
        if (!is_array($composerData)) {
            return;
        }

        if (isset($composerData['name']) && is_string($composerData['name'])) {
            $projectNode->addVariable('composer_name', new PlainTextInlineNode($composerData['name']));
        }

        if (isset($composerData['description']) && is_string($composerData['description'])) {
            $projectNode->addVariable('composer_description', new PlainTextInlineNode($composerData['description']));
        }

        if (isset($composerData['version']) && is_string($composerData['version'])) {
            if ($projectNode->getVersion() === null) {
                $projectNode->setVersion($composerData['version']);
            }
        }

        if (isset($composerData['type']) && is_string($composerData['type'])) {
            $projectNode->addVariable('composer_type', new PlainTextInlineNode($composerData['type']));
        }

        if (isset($composerData['license'])) {
            if (is_string($composerData['license'])) {
                $projectNode->addVariable('license', new PlainTextInlineNode($composerData['license']));
            } elseif (is_array($composerData['license'])) {
                $projectNode->addVariable(
                    'license',
                    new PlainTextInlineNode(sprintf('(%s)', implode(' or ', $composerData['license']))),
                );
            }
        }

        if (isset($composerData['keywords']) && is_array($composerData['keywords'])) {
            $projectNode->addKeywords($composerData['keywords']);
        }

        if (!isset($composerData['support']) || !is_array($composerData['support'])) {
            return;
        }

        foreach ($composerData['support'] as $key => $support) {
            if (!is_string($support)) {
                continue;
            }

            $projectNode->addVariable(
                'composer_support_' . $key,
                new PlainTextInlineNode($support),
            );
        }
    }
}
