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

namespace phpDocumentor\Guides\Cli\DependencyInjection;

use phpDocumentor\Guides\ApplicationTestCase;
use phpDocumentor\Guides\Cli\Validation\GithubActionsViolationFormat;
use phpDocumentor\Guides\Cli\Validation\GitlabViolationFormat;
use phpDocumentor\Guides\Cli\Validation\ViolationFormat;
use phpDocumentor\Guides\Cli\Validation\ViolationFormats;
use phpDocumentor\Guides\DependencyInjection\GuidesExtension;
use phpDocumentor\Guides\Validation\Violation;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

use function assert;

final class ViolationFormatWiringTest extends ApplicationTestCase
{
    public function testNoFormatIsChosenByDefault(): void
    {
        $this->prepareContainerWithFormat(null);

        self::assertNull($this->getViolationFormats()->getChosenFormat());
    }

    /** @param class-string<ViolationFormat> $expected */
    #[DataProvider('builtInFormatProvider')]
    public function testBuiltInFormatIsAvailable(string $format, string $expected): void
    {
        $this->prepareContainerWithFormat($format);

        self::assertInstanceOf($expected, $this->getViolationFormats()->getChosenFormat());
    }

    /** @return iterable<string, array{string, class-string<ViolationFormat>}> */
    public static function builtInFormatProvider(): iterable
    {
        yield 'github' => ['github', GithubActionsViolationFormat::class];
        yield 'gitlab' => ['gitlab', GitlabViolationFormat::class];
    }

    public function testAnExtensionCanAddAFormat(): void
    {
        $this->prepareContainerWithFormat('from-extension');

        self::assertSame(
            $this->getContainer()->get('test.violation_format'),
            $this->getViolationFormats()->getChosenFormat(),
        );
    }

    private function prepareContainerWithFormat(string|null $format): void
    {
        $this->prepareContainer(
            configuration: $format === null ? [] : [GuidesExtension::class => ['violation_format' => $format]],
            extraExtensions: [
                new class extends Extension implements CompilerPassInterface {
                    /** @param array<mixed> $configs */
                    public function load(array $configs, ContainerBuilder $container): void
                    {
                        // As an extension would register a format of its own
                        $format = new class implements ViolationFormat {
                            public function report(Violation $violation): void
                            {
                            }

                            public function finish(OutputInterface $output): void
                            {
                            }
                        };
                        $container->register('test.violation_format', $format::class)
                            ->setPublic(true)
                            ->addTag('phpdoc.guides.violation_format', ['format' => 'from-extension']);
                    }

                    public function process(ContainerBuilder $container): void
                    {
                        $container->getDefinition(ViolationFormats::class)->setPublic(true);
                    }

                    public function getAlias(): string
                    {
                        return 'test_violation_format';
                    }
                },
            ],
        );
    }

    private function getViolationFormats(): ViolationFormats
    {
        $violationFormats = $this->getContainer()->get(ViolationFormats::class);
        assert($violationFormats instanceof ViolationFormats);

        return $violationFormats;
    }
}
