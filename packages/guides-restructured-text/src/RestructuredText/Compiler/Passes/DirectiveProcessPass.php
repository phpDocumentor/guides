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

namespace phpDocumentor\Guides\RestructuredText\Compiler\Passes;

use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective as DirectiveHandler;
use phpDocumentor\Guides\RestructuredText\Directives\GeneralDirective;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use Psr\Log\LoggerInterface;

use function strtolower;

use const PHP_INT_MAX;

/** @implements NodeTransformer<DirectiveNode> */
final class DirectiveProcessPass implements NodeTransformer
{
    /** @var array<string, DirectiveHandler> */
    private array $directives;

    /** @param iterable<DirectiveHandler> $directives */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly GeneralDirective $generalDirective,
        iterable $directives = [],
    ) {
        foreach ($directives as $directive) {
            $this->registerDirective($directive);
        }
    }

    private function registerDirective(DirectiveHandler $directive): void
    {
        $this->directives[strtolower($directive->getName())] = $directive;
        foreach ($directive->getAliases() as $alias) {
            $this->directives[strtolower($alias)] = $directive;
        }
    }

    public function enterNode(Node $node, CompilerContext $compilerContext): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        $newNode = $this->getDirectiveHandler($node->getDirective())->createNode($node);
        if ($newNode === null) {
            return null;
        }

        $newNode->setClasses($node->getClasses());

        return $newNode;
    }

    private function getDirectiveHandler(Directive $directive): DirectiveHandler
    {
        return $this->directives[strtolower($directive->getName())] ?? $this->generalDirective;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DirectiveNode;
    }

    public function getPriority(): int
    {
        return PHP_INT_MAX;
    }
}
