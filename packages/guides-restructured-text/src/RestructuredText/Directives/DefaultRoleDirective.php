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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * sets the default interpreted text role, the role that is used for interpreted text without an explicit role.
 *
 * https://docutils.sourceforge.io/docs/ref/rst/directives.html#default-role
 */
final class DefaultRoleDirective extends ActionDirective
{
    public function getName(): string
    {
        return 'default-role';
    }

    public function processAction(
        BlockContext $blockContext,
        Directive $directive,
    ): void {
        $name = $directive->getData();
        $blockContext->getDocumentParserContext()->getTextRoleFactoryForDocument()->setDefaultTextRole($name);
    }
}
