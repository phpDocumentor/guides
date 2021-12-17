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

namespace phpDocumentor\Guides\References;

use phpDocumentor\Guides\RenderContext;

/**
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/roles.html
 */
class Doc extends Reference
{
    /** @var string */
    private $name;

    /** @var Resolver */
    private $resolver;

    /**
     * Used with "ref" - it means the dependencies added in found()
     * must be resolved to their final path later (they are not
     * already document names).
     *
     * @var bool
     */
    private $dependenciesMustBeResolved;

    public function __construct(string $name = 'doc', bool $dependenciesMustBeResolved = false)
    {
        $this->name = $name;
        $this->resolver = new Resolver();
        $this->dependenciesMustBeResolved = $dependenciesMustBeResolved;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function resolve(RenderContext $environment, string $data): ?ResolvedReference
    {
        return $this->resolver->resolve($environment, $data);
    }

    public function found(ReferenceBuilder $referenceRegistry, string $data): void
    {
        $referenceRegistry->addDependency($data, $this->dependenciesMustBeResolved);
    }
}
