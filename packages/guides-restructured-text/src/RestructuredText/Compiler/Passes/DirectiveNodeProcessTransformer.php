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
use phpDocumentor\Guides\Compiler\ReverseNodeTransformer;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective as DirectiveHandler;
use phpDocumentor\Guides\RestructuredText\Directives\GeneralDirective;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function array_merge;
use function explode;
use function is_string;
use function strtolower;

use const PHP_INT_MAX;

/** @implements ReverseNodeTransformer<DirectiveNode> */
final class DirectiveNodeProcessTransformer implements ReverseNodeTransformer
{
    /** @var array<string, DirectiveHandler> */
    private array $directives;

    /** @param iterable<DirectiveHandler> $directives */
    public function __construct(
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
        if (!$node instanceof DocumentNode) {
            return $node;
        }

        foreach ($node->getVariables() as $name => $value) {
            if (!$value instanceof DirectiveNode) {
                continue;
            }

            $newNode = $this->getDirectiveHandler($value->getDirective())->createNode($value, $compilerContext);
            if ($newNode === null) {
                continue;
            }

            $node->addVariable($name, $newNode);
        }

        return $node;
    }

    public function leaveNode(Node $node, CompilerContext $compilerContext): Node|null
    {
        if ($node instanceof DirectiveNode === false) {
            return $node;
        }

        $newNode = $this->getDirectiveHandler($node->getDirective())->createNode($node, $compilerContext);
        if ($newNode === null) {
            return null;
        }

        $newNode->setClasses(array_merge($newNode->getClasses(), $node->getClasses()));

        // Matches DirectiveRule::postProcessNode() for the old (non-upgraded)
        // dispatch: apply directive options createNode() did not consume
        // itself as node options (`:class:` becomes CSS classes instead).
        $optionsForNode = [];
        foreach ($node->getDirective()->getOptions() as $option) {
            if (!is_string($option->getValue())) {
                continue;
            }

            if ($option->getName() === 'class') {
                $newNode->setClasses(array_merge($newNode->getClasses(), explode(' ', $option->getValue())));
                continue;
            }

            $optionsForNode[$option->getName()] = $option->getValue();
        }

        $newNode = $newNode->withOptions(array_merge($optionsForNode, $newNode->getOptions()));
        $variable = $node->getDirective()->getVariable();
        if ($variable !== '') {
            $compilerContext->getDocumentNode()->addVariable($variable, $newNode);
        }

        return $newNode;
    }

    private function getDirectiveHandler(Directive $directive): DirectiveHandler
    {
        return $this->directives[strtolower($directive->getName())] ?? $this->generalDirective;
    }

    public function supports(Node $node): bool
    {
        return $node instanceof DocumentNode || $node instanceof DirectiveNode;
    }

    public function getPriority(): int
    {
        return PHP_INT_MAX - 1;
    }
}
