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

use phpDocumentor\Guides\Validation\Severity;
use phpDocumentor\Guides\Validation\Violation;
use Symfony\Component\Console\Output\OutputInterface;

use function implode;
use function preg_replace;
use function sprintf;
use function str_replace;

/**
 * Prints the violations as GitHub Actions workflow commands, which GitHub shows as annotations on a pull request.
 */
final class GithubActionsViolationFormat implements ViolationFormat
{
    /** @var list<string> */
    private array $annotations = [];

    public function __construct(private readonly AnnotationDetails $details)
    {
    }

    public function report(Violation $violation): void
    {
        $properties = [];
        $file = $this->details->getFile($violation);
        if ($file !== null) {
            $properties[] = 'file=' . self::escapeProperty($file);
            $line = $this->details->getLine($violation);
            if ($line !== null) {
                $properties[] = 'line=' . $line;
            }
        }

        $properties[] = 'title=' . self::escapeProperty($violation->identifier);

        $this->annotations[] = sprintf(
            '::%s %s::%s',
            match ($violation->severity) {
                Severity::Warning => 'warning',
                Severity::Error => 'error',
            },
            implode(',', $properties),
            self::escapeData($this->details->getMessage($violation)),
        );
    }

    public function finish(OutputInterface $output): void
    {
        // Each workflow command has to be on a line of its own in the output of the step, also with --quiet
        $output->writeln($this->annotations, OutputInterface::VERBOSITY_QUIET);
    }

    private static function escapeData(string $data): string
    {
        // GitHub would notify the user named in a mention like "@user"
        $data = (string) preg_replace('/(?<![\w`])@([\w-]+)/', '`@$1`', $data);

        return str_replace(['%', "\r", "\n"], ['%25', '%0D', '%0A'], $data);
    }

    private static function escapeProperty(string $property): string
    {
        return str_replace(['%', "\r", "\n", ':', ','], ['%25', '%0D', '%0A', '%3A', '%2C'], $property);
    }
}
