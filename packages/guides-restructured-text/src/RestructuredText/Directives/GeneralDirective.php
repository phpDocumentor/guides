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

use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use phpDocumentor\Guides\Settings\SettingsManager;

use function explode;
use function in_array;
use function str_contains;

/**
 * A catch-all directive, the content is treated as content, the options passed on
 */
final class GeneralDirective extends SubDirective
{
    /** @param Rule<CollectionNode> $startingRule */
    public function __construct(
        Rule $startingRule,
        private readonly SettingsManager $settingsManager,
    ) {
        parent::__construct($startingRule);
    }

    public function getName(): string
    {
        return '';
    }

    /** {@inheritDoc}
     *
     * @param Directive $directive
     */
    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        if (str_contains($directive->getName(), ':')) {
            [$domainName, $directiveName] = explode(':', $directive->getName());
            if (in_array($domainName, $this->settingsManager->getProjectSettings()->getIgnoredDomains(), true)) {
                return $collectionNode;
            }
        }

        return new GeneralDirectiveNode(
            $directive->getName(),
            $directive->getData(),
            $directive->getDataNode() ?? new InlineCompoundNode(),
            $collectionNode->getChildren(),
        );
    }
}
