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
            $allowElementMethodCalls = array_values(array_filter($definition->getMethodCalls(), static fn (array $call) => $call[0] === 'allowElement'));
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
    }
}
