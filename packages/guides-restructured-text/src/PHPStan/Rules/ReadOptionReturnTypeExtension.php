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

namespace phpDocumentor\Guides\PHPStan\Rules;

use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

use function count;

final class ReadOptionReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return BaseDirective::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'readOption';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope,
    ): Type|null {
        $args = $methodCall->getArgs();
        if (count($args) < 2) {
            return null;
        }

        $optionNameType = $scope->getType($args[1]->value);
        $constantStrings = $optionNameType->getConstantStrings();
        if (count($constantStrings) !== 1) {
            return new MixedType();
        }

        $callerType = $scope->getType($methodCall->var);
        $classReflections = $callerType->getObjectClassReflections();
        if (count($classReflections) === 0) {
            return new MixedType();
        }

        return $this->resolveReturnType($classReflections[0], $constantStrings[0]->getValue());
    }

    public function resolveReturnType(ClassReflection $classReflection, string $optionName): Type
    {
        foreach ($classReflection->getAttributes() as $attribute) {
            if ($attribute->getName() !== Option::class) {
                continue;
            }

            $argumentTypes = $attribute->getArgumentTypes();

            $nameType = $argumentTypes['name'] ?? null;
            if ($nameType === null || count($nameType->getConstantStrings()) !== 1) {
                continue;
            }

            if ($nameType->getConstantStrings()[0]->getValue() !== $optionName) {
                continue;
            }

            return TypeCombinator::union(
                $this->resolveBaseType($argumentTypes['type'] ?? null),
                $this->resolveDefaultType($argumentTypes['default'] ?? null),
            );
        }

        return new MixedType();
    }

    private function resolveBaseType(Type|null $typeArgument): Type
    {
        $enumCase = $typeArgument?->getEnumCaseObject();
        if ($enumCase !== null) {
            return match ($enumCase->getEnumCaseName()) {
                'Integer' => new IntegerType(),
                'Boolean' => new BooleanType(),
                'Array' => new ArrayType(new MixedType(), new MixedType()),
                default => new StringType(),
            };
        }

        return new StringType();
    }

    private function resolveDefaultType(Type|null $defaultArgument): Type
    {
        if ($defaultArgument === null) {
            return new NullType();
        }

        if (
            $defaultArgument->isNull()->yes()
            || $defaultArgument->isBoolean()->yes()
            || $defaultArgument->isFloat()->yes()
            || $defaultArgument->isInteger()->yes()
            || $defaultArgument->isString()->yes()
        ) {
            return $defaultArgument;
        }

        return new MixedType();
    }
}
