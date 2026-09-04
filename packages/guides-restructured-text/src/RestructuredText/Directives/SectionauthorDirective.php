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

use phpDocumentor\Guides\Nodes\AuthorNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use Psr\Log\LoggerInterface;

use function preg_match;

/**
 * When the default domain contains a class directive, this directive will be shadowed. Therefore, Sphinx re-exports it as rst-class.
 *
 * See https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html#rstclass
 */
#[Attributes\Directive(name: 'sectionauthor', aliases: ['codeauthor'])]
final class SectionauthorDirective extends BaseDirective
{
    /** @see https://regex101.com/r/vGy4Uu/1 */
    public const NAME_EMAIL_REGEX = '/^(?P<name>[\w\s]+)(?: <(?P<email>[^>]+)>)?$/';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function createNode(DirectiveNode $directiveNode): Node|null
    {
        $directive = $directiveNode->getDirective();
        $input = $directive->getData();
        $directiveName = $directive->getName();
        if ($input === '') {
            $this->logger->warning('`.. ' . $directiveName . ' ::` directive could not be parsed: `' . $input . '`', $directiveNode->getSourceLocation()->toLoggerInformation());

            return null;
        }

        if (!preg_match(self::NAME_EMAIL_REGEX, $input, $matches)) {
            $this->logger->warning('Content of `.. ' . $directiveName . ':: name <email>` must specify a name and can also specify an email', $directiveNode->getSourceLocation()->toLoggerInformation());

            return null;
        }

        $name = $matches['name'];
        $email = $matches['email'] ?? null;
        $context = match ($directiveName) {
            'sectionauthor' => AuthorNode::CONTEXT_SECTION,
            default => AuthorNode::CONTEXT_CODE,
        };

        return new AuthorNode($name, [], $context, $email);
    }
}
