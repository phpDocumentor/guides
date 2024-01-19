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

namespace phpDocumentor\Guides\RestructuredText\Parser;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective as DirectiveHandler;

final class DummyBaseDirective extends DirectiveHandler
{
    private string $name = 'dummy';

    public function getName(): string
    {
        return $this->name;
    }

    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        return new DummyNode($directive->getVariable(), $directive->getData(), $directive->getOptions());
    }
}
