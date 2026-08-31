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

namespace phpDocumentor\Guides\DependencyInjection;

use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_filter;
use function array_values;

class GuidesExtensionTest extends TestCase
{
    /**
     * @param array<array<mixed>> $configs
     * @param callable(ContainerBuilder):void $assertions
     */
    #[DataProvider('provideConfigs')]
    public function testLoad(array $configs, callable $assertions): void
    {
        $container = new ContainerBuilder();

        $extension = new GuidesExtension();
        $extension->load($configs, $container);

        $assertions($container);
    }

    /** @return iterable<string, array{array<mixed>, callable(ContainerBuilder):void}> */
    public static function provideConfigs(): iterable
    {
        $sanitizerAssertions = static function (ContainerBuilder $container): void {
            self::assertTrue($container->hasDefinition('phpdoc.guides.raw_node.sanitizer.default'));

            $definition = $container->getDefinition('phpdoc.guides.raw_node.sanitizer.default');
            /** @var list<array{0: string, 1: array<mixed>}> $methodCalls */
            $methodCalls = $definition->getMethodCalls();
            $allowElementMethodCalls = array_values(array_filter($methodCalls, static fn (array $call): bool => $call[0] === 'allowElement'));
            self::assertCount(1, $allowElementMethodCalls);
            self::assertSame(['object', ['type', 'data', 'alt']], $allowElementMethodCalls[0][1]);
        };

        yield 'sanitizer' => [
            [
                [
                    'raw_node' => [
                        'sanitizers' => [
                            'default' => [
                                'allow_elements' => [
                                    'object' => ['type', 'data', 'alt'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $sanitizerAssertions,
        ];

        yield 'sanitizer XML' => [
            [
                [
                    'raw_node' => [
                        'sanitizer' => [
                            'name' => 'default',
                            'allow_element' => [
                                [
                                    'name' => 'object',
                                    'attribute' => ['type', 'data', 'alt'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $sanitizerAssertions,
        ];

        // ContainerFactory::loadExtensionConfig() feeds a raw PHP array into this tree without going
        // through XmlFileLoader, so a native float never meets the string handling that lives there.
        // `(string) 3.0` is "3": the trailing zero has to survive this path on its own.
        yield 'project version and release as float' => [
            [['project' => ['version' => 3.0, 'release' => 3.0]]],
            static function (ContainerBuilder $container): void {
                $settings = self::projectSettings($container);
                self::assertSame('3.0', $settings->getVersion());
                self::assertSame('3.0', $settings->getRelease());
            },
        ];

        yield 'project version as string keeps its own form' => [
            [['project' => ['version' => '0.10']]],
            static function (ContainerBuilder $container): void {
                self::assertSame('0.10', self::projectSettings($container)->getVersion());
            },
        ];

        // An explicit null means "not configured", so the default has to survive it. Turning it into
        // a literal renders the string "NULL" as the project version.
        yield 'project version as null leaves the default' => [
            [['project' => ['version' => null]]],
            static function (ContainerBuilder $container): void {
                self::assertSame('', self::projectSettings($container)->getVersion());
            },
        ];
    }

    /** Reads back the ProjectSettings the extension hands to the SettingsManager definition. */
    private static function projectSettings(ContainerBuilder $container): ProjectSettings
    {
        $calls = array_values(array_filter(
            $container->getDefinition(SettingsManager::class)->getMethodCalls(),
            static fn (array $call) => $call[0] === 'setProjectSettings',
        ));

        self::assertCount(1, $calls);
        $settings = $calls[0][1][0];
        self::assertInstanceOf(ProjectSettings::class, $settings);

        return $settings;
    }
}
