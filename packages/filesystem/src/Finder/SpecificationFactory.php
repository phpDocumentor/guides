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

namespace phpDocumentor\FileSystem\Finder;

use Flyfinder\Path as FlyFinderPath;
use Flyfinder\Specification\CompositeSpecification;
use Flyfinder\Specification\Glob;
use Flyfinder\Specification\HasExtension;
use Flyfinder\Specification\InPath;
use Flyfinder\Specification\IsHidden;
use Flyfinder\Specification\NotSpecification;
use Flyfinder\Specification\SpecificationInterface;
use phpDocumentor\FileSystem\Path;

use function str_ends_with;
use function strpbrk;
use function substr;

/**
 * Factory class to build Specification used by FlyFinder when reading files to process.
 */
final class SpecificationFactory implements SpecificationFactoryInterface
{
    /**
     * Creates a SpecificationInterface object based on the ignore and extension parameters.
     *
     * @param list<string|Path> $paths
     * @param list<string> $extensions
     */
    public function create(array $paths, Exclude $ignore, array $extensions): SpecificationInterface
    {
        /** @var ?Glob $pathSpec */
        $pathSpec = null;
        foreach ($paths as $path) {
            if ($path instanceof Path) {
                $condition = new InPath(new FlyFinderPath((string) $path));
            } else {
                $condition = new Glob($path);
            }

            if ($pathSpec === null) {
                $pathSpec = $condition;
                continue;
            }

            $pathSpec = $pathSpec->orSpecification($condition);
        }

        /** @var ?CompositeSpecification $ignoreSpec */
        $ignoreSpec = null;
        foreach ($ignore->getPaths() as $path) {
            $condition = $this->createIgnoreSpecification($path);
            if ($ignoreSpec === null) {
                $ignoreSpec = $condition;
                continue;
            }

            $ignoreSpec = $ignoreSpec->orSpecification($condition);
        }

        if ($ignore->excludeHidden()) {
            $ignoreSpec = $ignoreSpec === null
                ? new IsHidden()
                : $ignoreSpec->orSpecification(new IsHidden());
        }

        $result = new HasExtension($extensions);
        if ($ignoreSpec !== null) {
            $result = $result->andSpecification(new NotSpecification($ignoreSpec));
        }

        if ($pathSpec !== null) {
            $result = $result->andSpecification($pathSpec);
        }

        return $result;
    }

    /**
     * A path without wildcards excludes that file, or that directory with everything in it. So does "<path>/**\/*",
     * which a glob would only match below the directory, and without letting the finder skip the directory.
     */
    private function createIgnoreSpecification(string $path): CompositeSpecification
    {
        $directory = str_ends_with($path, '/**/*') ? substr($path, 0, -5) : $path;
        if (strpbrk($directory, '*?[{\\') === false) {
            return new WithinPath($directory);
        }

        return new Glob($path);
    }
}
