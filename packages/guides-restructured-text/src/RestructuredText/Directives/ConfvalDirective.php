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

use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\AnchorNormalizer;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Directive;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Nodes\ConfvalNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericLinkProvider;
use Psr\Log\LoggerInterface;

use function in_array;
use function trim;

/**
 * The confval directive configuration values.
 *
 * https://sphinx-toolbox.readthedocs.io/en/stable/extensions/confval.html
 */
#[Directive(name: 'confval', parseUndeclaredOptionsAsInlineMarkup: true)]
#[Option(name: 'name', description: 'Id of the configuration value, used for linking to it.')]
#[Option(name: 'type', type: OptionType::InlineMarkup, description: 'Type of the configuration value, e.g. "string", "int", etc.')]
#[Option(name: 'required', type: OptionType::Boolean, default: false, description: 'Whether the configuration value is required or not.')]
#[Option(name: 'default', type: OptionType::InlineMarkup, description: 'Default value of the configuration value, if any.')]
#[Option(name: 'noindex', type: OptionType::Boolean, default: false, description: 'Whether the configuration value should not be indexed.')]
final class ConfvalDirective extends BaseDirective
{
    public const NAME = 'confval';

    public function __construct(
        GenericLinkProvider $genericLinkProvider,
        private readonly AnchorNormalizer $anchorReducer,
        private readonly LoggerInterface|null $logger = null,
    ) {
        $genericLinkProvider->addGenericLink(self::NAME, ConfvalNode::LINK_TYPE, ConfvalNode::LINK_PREFIX);
    }

    public function createNode(DirectiveNode $directiveNode, CompilerContextInterface $compilerContext): Node
    {
        $directive = $directiveNode->getDirective();
        $id = $directive->getData();
        if ($directive->hasOption('name')) {
            $id = (string) $directive->getOption('name')->getValue();
        }

        $id = $this->anchorReducer->reduceAnchor($id);
        $additionalOptions = [];
        if (trim($directive->getData()) === '') {
            if ($this->logger !== null) {
                $this->logger->warning('A directive must have a title: ..  confval:: [some_title]', $compilerContext->getLoggerInformation());
            }
        }

        $type = $this->readOption($directive, 'type');
        $type = $type instanceof InlineCompoundNode ? $type : null;

        $required = (bool) $this->readOption($directive, 'required');

        $default = $this->readOption($directive, 'default');
        $default = $default instanceof InlineCompoundNode ? $default : null;

        $noindex = (bool) $this->readOption($directive, 'noindex');

        foreach ($directive->getOptions() as $option) {
            if (in_array($option->getName(), ['type', 'required', 'default', 'noindex', 'name'], true)) {
                continue;
            }

            $node = $option->getNode();
            if ($node === null) {
                continue;
            }

            $additionalOptions[$option->getName()] = $node;
        }

        return new ConfvalNode($id, $directive->getData(), $type, $required, $default, $additionalOptions, $directiveNode->getChildren(), $noindex);
    }
}
