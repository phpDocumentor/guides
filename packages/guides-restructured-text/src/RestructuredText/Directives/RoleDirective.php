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
use phpDocumentor\Guides\RestructuredText\TextRoles\BaseTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericTextRole;
use Psr\Log\LoggerInterface;

use function is_string;
use function preg_match;
use function sprintf;
use function trim;

/**
 * The "role" directive dynamically creates a custom interpreted text role and registers it with the parser.
 *
 * https://docutils.sourceforge.io/docs/ref/rst/directives.html#role
 */
final class RoleDirective extends ActionDirective
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return 'role';
    }

    public function processAction(
        BlockContext $blockContext,
        Directive $directive,
    ): void {
        $name = $directive->getData();
        $role = 'span';
        if (preg_match('/^([A-Za-z-]*)\(([A-Za-z-]*)\)$/', trim($name), $match) === 1) {
            $name = $match[1];
            $role = $match[2];
        }

        $baseRole = $blockContext->getDocumentParserContext()->getTextRoleFactoryForDocument()->getTextRole($role);
        if (!$baseRole instanceof BaseTextRole) {
            $this->logger->error(
                sprintf('Text role "%s", class %s cannot be extended. ', $role, $baseRole::class),
                $blockContext->getLoggerInformation(),
            );

            return;
        }

        $customRole = $baseRole->withName($name);
        if (is_string($directive->getOption('class')->getValue())) {
            $customRole->setClass($directive->getOption('class')->getValue());
        } else {
            $customRole->setClass($name);
        }

        if ($customRole instanceof GenericTextRole) {
            $customRole->setBaseRole($role);
        }

        $blockContext->getDocumentParserContext()->getTextRoleFactoryForDocument()->replaceTextRole($customRole);
    }
}
