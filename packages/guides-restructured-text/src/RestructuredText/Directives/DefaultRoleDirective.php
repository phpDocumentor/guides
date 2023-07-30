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

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\DefaultTextRoleFactory;

/**
 * sets the default interpreted text role, the role that is used for interpreted text without an explicit role.
 *
 * https://docutils.sourceforge.io/docs/ref/rst/directives.html#default-role
 */
class DefaultRoleDirective extends BaseDirective
{
    public function __construct(
        private readonly DefaultTextRoleFactory $textRoleFactory,
    ) {
    }

    public function getName(): string
    {
        return 'default-role';
    }

    public function process(
        DocumentParserContext $documentParserContext,
        Directive $directive,
    ): Node|null {
        $name = $directive->getData();
        $this->textRoleFactory->setDefaultTextRole($name);

        return null;
    }
}
