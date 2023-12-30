<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\Configuration\ConfigurationBlockNode;
use phpDocumentor\Guides\Nodes\Configuration\ConfigurationTab;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

use function assert;
use function get_debug_type;

final class ConfigurationBlockDirective extends SubDirective
{
    private SluggerInterface $slugger;

    /**
     * @param Rule<CollectionNode> $startingRule
     * @param array<string, string> $languageLabels
     */
    public function __construct(
        private LoggerInterface $logger,
        Rule $startingRule,
        private readonly array $languageLabels = [],
    ) {
        parent::__construct($startingRule);

        $this->slugger = new AsciiSlugger();
    }

    public function getName(): string
    {
        return 'configuration-block';
    }

    protected function processSub(
        BlockContext $blockContext,
        CollectionNode $collectionNode,
        Directive $directive,
    ): Node|null {
        $tabs = [];
        foreach ($collectionNode->getValue() as $child) {
            if (!$child instanceof CodeNode) {
                $this->logger->warning('The ".. configuration-block::" directive only supports code blocks, "' . get_debug_type($child) . '" given.');

                continue;
            }

            $language = $child->getLanguage();
            assert($language !== null);

            $label = $this->languageLabels[$language] ?? $this->slugger->slug($language, ' ')->title()->toString();

            $tabs[] = new ConfigurationTab(
                $label,
                $this->slugger->slug($label)->lower()->toString(),
                $child,
            );
        }

        return new ConfigurationBlockNode($tabs);
    }
}
