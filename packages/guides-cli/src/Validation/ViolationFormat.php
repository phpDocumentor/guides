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

namespace phpDocumentor\Guides\Cli\Validation;

use phpDocumentor\Guides\Validation\ViolationReporter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A format to report the problems found in the documentation source in, in addition to the log.
 *
 * Register an implementation with the tag "phpdoc.guides.violation_format" and the name of the format as "format"
 * attribute. When that name is the "violation-format" setting, it receives every violation during the run.
 */
interface ViolationFormat extends ViolationReporter
{
    /**
     * Called once at the end of the run, to write the collected violations wherever this format needs them, like
     * the output of the command, a file or the API of a code review tool.
     */
    public function finish(OutputInterface $output): void;
}
