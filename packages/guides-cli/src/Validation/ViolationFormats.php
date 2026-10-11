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

use InvalidArgumentException;
use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Validation\Violation;
use phpDocumentor\Guides\Validation\ViolationReporter;
use Symfony\Contracts\Service\ServiceProviderInterface;

use function array_keys;
use function implode;
use function sprintf;

/**
 * Passes each violation on to the format chosen with the "violation-format" setting, out of all formats registered
 * with the tag "phpdoc.guides.violation_format".
 */
final class ViolationFormats implements ViolationReporter
{
    /** @param ServiceProviderInterface<ViolationFormat> $formats The formats, by the name in their "format" attribute */
    public function __construct(
        private readonly ServiceProviderInterface $formats,
        private readonly SettingsManager $settingsManager,
    ) {
    }

    public function report(Violation $violation): void
    {
        $this->getChosenFormat()?->report($violation);
    }

    /** @throws InvalidArgumentException When no format is registered under the chosen name. */
    public function getChosenFormat(): ViolationFormat|null
    {
        $name = $this->settingsManager->getProjectSettings()->getViolationFormat();
        if ($name === null || $name === '') {
            return null;
        }

        if (!$this->formats->has($name)) {
            throw new InvalidArgumentException(sprintf(
                'The violation format "%s" is not available, use one of: %s.',
                $name,
                implode(', ', array_keys($this->formats->getProvidedServices())),
            ));
        }

        return $this->formats->get($name);
    }
}
