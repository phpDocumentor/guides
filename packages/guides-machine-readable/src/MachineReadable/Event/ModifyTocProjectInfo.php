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

namespace phpDocumentor\Guides\MachineReadable\Event;

use phpDocumentor\Guides\MachineReadable\Toc\TocProject;
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
 * Listeners see the section as earlier listeners left it, and keys they add
 * with {@see TocProject::setExtra()} are written in the order they were added.
 */
final class ModifyTocProjectInfo
{
    public function __construct(
        private readonly TocProject $project,
        private readonly ProjectNode $projectNode,
    ) {
    }

    public function getProject(): TocProject
    {
        return $this->project;
    }

    public function getProjectNode(): ProjectNode
    {
        return $this->projectNode;
    }
}
