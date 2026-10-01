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
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Directive as DirectiveAttribute;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective as DirectiveHandler;
use phpDocumentor\Guides\RestructuredText\Directives\ValueType;

#[DirectiveAttribute(name: 'dummy-integer', valueType: ValueType::Integer)]
final class DummyIntegerValueDirective extends DirectiveHandler
{
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node {
        $value = ($directive->getDataNode() !== null ? 'inline:' : 'raw:') . $directive->getData();

        return new DummyNode($directive->getVariable(), $value, $directive->getOptions());
    }
}
