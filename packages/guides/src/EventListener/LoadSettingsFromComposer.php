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

namespace phpDocumentor\Guides\EventListener;

use phpDocumentor\Guides\Event\PostProjectNodeCreated;
use phpDocumentor\Guides\Settings\ComposerSettingsLoader;

use function dirname;
use function file_exists;
use function getcwd;
use function is_string;

final class LoadSettingsFromComposer
{
    public function __construct(private readonly ComposerSettingsLoader $composerSettingsLoader)
    {
    }

    public function __invoke(PostProjectNodeCreated $event): void
    {
        $workDir = getcwd();
        if ($workDir === false) {
            return;
        }

        $composerjson = $this->findComposerJson($workDir);
        if (!is_string($composerjson)) {
            return;
        }

        $projectNode = $event->getProjectNode();
        $settings = $event->getSettings();

        $this->composerSettingsLoader->loadSettings($projectNode, $settings, $composerjson);
    }

    private function findComposerJson(string $currentDir): string|null
    {
        // Navigate up the directory structure until finding the composer.json file
        while (!file_exists($currentDir . '/composer.json') && $currentDir !== '/') {
            $currentDir = dirname($currentDir);
        }

        // If found, return the path to the composer.json file; otherwise, return null
        return file_exists($currentDir . '/composer.json') ? $currentDir . '/composer.json' : null;
    }
}
