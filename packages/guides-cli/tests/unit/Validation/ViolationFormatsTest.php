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
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\Validation\Violation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class ViolationFormatsTest extends TestCase
{
    private ProjectSettings $settings;

    protected function setUp(): void
    {
        $this->settings = new ProjectSettings();
    }

    public function testOnlyTheChosenFormatReceivesTheViolation(): void
    {
        $violation = Violation::warning('rst.code-block.empty', 'The code-block has no content.');
        $chosen = $this->createMock(ViolationFormat::class);
        $chosen->expects(self::once())->method('report')->with($violation);
        $other = $this->createMock(ViolationFormat::class);
        $other->expects(self::never())->method('report');
        $this->settings->setViolationFormat('bitbucket');

        $formats = $this->createViolationFormats(['github' => $other, 'bitbucket' => $chosen]);
        $formats->report($violation);

        self::assertSame($chosen, $formats->getChosenFormat());
    }

    public function testNoFormatIsChosenByDefault(): void
    {
        $github = $this->createMock(ViolationFormat::class);
        $github->expects(self::never())->method('report');

        $formats = $this->createViolationFormats(['github' => $github]);
        $formats->report(Violation::warning('rst.code-block.empty', 'The code-block has no content.'));

        self::assertNull($formats->getChosenFormat());
    }

    public function testUnknownFormatNamesTheAvailableOnes(): void
    {
        $this->settings->setViolationFormat('gitlab');
        $formats = $this->createViolationFormats([
            'github' => $this->createMock(ViolationFormat::class),
            'bitbucket' => $this->createMock(ViolationFormat::class),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The violation format "gitlab" is not available, use one of: github, bitbucket.');

        $formats->getChosenFormat();
    }

    /** @param array<string, ViolationFormat> $formats */
    private function createViolationFormats(array $formats): ViolationFormats
    {
        $factories = [];
        foreach ($formats as $name => $format) {
            $factories[$name] = static fn (): ViolationFormat => $format;
        }

        /** @var ServiceLocator<ViolationFormat> $locator */
        $locator = new ServiceLocator($factories);

        return new ViolationFormats($locator, new SettingsManager($this->settings));
    }
}
