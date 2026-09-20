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

namespace phpDocumentor\Guides\Llms\Event;

use phpDocumentor\Guides\Nodes\ProjectNode;

/**
 * The "project" section of "toc.json", before it is written.
 *
 * What a manual states about itself once, at the top, rather than per page.
 * The renderer fills in what every project has -- its title and its version --
 * and dispatches this so a theme can correct those or add what only it knows.
 * The TYPO3 theme, for one, adds the permalink pattern its manuals are
 * addressed by and normalizes the version of an unversioned manual to "main".
 *
 * Listeners see the section in the order earlier listeners left it, and keys
 * they add are written in the order they were added.
 */
final class ModifyTocProject
{
    /** @param array<string, mixed> $project */
    public function __construct(
        private array $project,
        private readonly ProjectNode $projectNode,
    ) {
    }

    /** @return array<string, mixed> */
    public function getProject(): array
    {
        return $this->project;
    }

    /** @param array<string, mixed> $project */
    public function setProject(array $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }
}
