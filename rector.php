<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);
    $rectorConfig->rule(Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector::class);
    $rectorConfig->rule(Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictGetterMethodReturnTypeRector::class);
    $rectorConfig->rule(Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector::class);
    $rectorConfig->rule(Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector::class);
    $rectorConfig->rule(Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector::class);
    $rectorConfig->rule(Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector::class);
    $rectorConfig->rule(Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector::class);
    $rectorConfig->importNames();


    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81
    ]);
};
