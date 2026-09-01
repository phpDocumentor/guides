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

namespace phpDocumentor\Guides\RestructuredText\PHPStan;

use phpDocumentor\Guides\PHPStan\Rules\ReadOptionReturnTypeExtension;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Testing\PHPStanTestCaseTrait;
use PHPStan\Type\VerbosityLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReadOptionReturnTypeExtensionTest extends TestCase
{
    use PHPStanTestCaseTrait;

    private ReadOptionReturnTypeExtension $subject;

    protected function setUp(): void
    {
        $this->subject = new ReadOptionReturnTypeExtension();
    }

    /** @return string[][] */
    public static function optionTypeProvider(): array
    {
        return [
            'string without default' => ['name', 'string|null'],
            'string with default' => ['title', 'string'],
            'integer with default' => ['count', 'int'],
            'boolean with default' => ['enabled', 'bool'],
            'array without default' => ['tags', 'array|null'],
            'string with float default' => ['float', '1.5|string'],
        ];
    }

    #[DataProvider('optionTypeProvider')]
    public function testResolvesDeclaredOptionTypes(string $optionName, string $expectedType): void
    {
        $type = $this->subject->resolveReturnType($this->fixtureReflection(), $optionName);

        self::assertSame($expectedType, $type->describe(VerbosityLevel::precise()));
    }

    public function testUnknownOptionResolvesToMixed(): void
    {
        $type = $this->subject->resolveReturnType($this->fixtureReflection(), 'unknown');

        self::assertSame('mixed', $type->describe(VerbosityLevel::precise()));
    }

    public function testClassWithoutOptionsResolvesToMixed(): void
    {
        $reflection = $this->reflectionProvider()->getClass(BaseDirective::class);

        $type = $this->subject->resolveReturnType($reflection, 'name');

        self::assertSame('mixed', $type->describe(VerbosityLevel::precise()));
    }

    public function testExtensionAppliesToBaseDirective(): void
    {
        self::assertSame(BaseDirective::class, $this->subject->getClass());
    }

    private function fixtureReflection(): ClassReflection
    {
        return $this->reflectionProvider()->getClass(ReadOptionFixtureDirective::class);
    }

    private function reflectionProvider(): ReflectionProvider
    {
        return self::getContainer()->getByType(ReflectionProvider::class);
    }
}
