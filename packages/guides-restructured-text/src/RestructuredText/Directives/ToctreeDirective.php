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

use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;

/**
 * Sphinx based Toctree directive.
 *
 * This directive has an issue, as the related documents are resolved on parse, but during the rendering
 * we are using the {@see Metas} to collect the titles of those documents. There is some step missing in our process
 * which could be resolved by using https://github.com/phpDocumentor/guides/pull/21?
 *
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#table-of-contents
 *
 * Parameters:
 *
 * :caption:
 *      (Text with inline markup)
 *      Caption to be displayed above the menu
 *
 * :depth:
 *     (integer)
 *     Maximum depth of the menu to display. Does not affect other menus.
 *
 * :glob:
 *     (bool)
 *     If true glob patterns containing stars are considered during menu building.
 *     The entries are added to the Document Tree. Orphans are ignored
 *
 * :globExclude:
 *     (comma separated string list)
 *     paths to be excluded from the glob patterns
 *
 * :hidden:
 *     (bool)
 *     The menu will not be displayed within the content and is only used to
 *     change the global document tree.
 *
 * :reversed:
 *     (bool)
 *     Display documents in reversed order. They are also added to the document
 *     tree in reversed order and will be displayed in that order where ever a menu
 *     is displayed
 *
 * :titlesonly:
 *     Do not display the headlines of the current or sub documents, only display
 *     page titles.
 *
 * :maxdepth:
 *     Synonym of :depth:, depth prevails if both are set.
 */
final class ToctreeDirective extends BaseDirective
{
    private SettingsManager $settingsManager;

    /** @param Rule<InlineCompoundNode> $startingRule */
    public function __construct(
        private readonly ToctreeBuilder $toctreeBuilder,
        private readonly Rule $startingRule,
        SettingsManager|null $settingsManager = null,
    ) {
        // if for backward compatibility reasons no settings manager was passed, use the defaults
        $this->settingsManager = $settingsManager ?? new SettingsManager(new ProjectSettings());
    }

    public function getName(): string
    {
        return 'toctree';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $parserContext = $blockContext->getDocumentParserContext()->getParser()->getParserContext();
        $options = $directive->getOptions();
        $indexName = $this->settingsManager->getProjectSettings()->getIndexName();
        $options['globExclude'] ??= new DirectiveOption('globExclude', $indexName);

        $toctreeFiles = $this->toctreeBuilder->buildToctreeEntries(
            $parserContext,
            $blockContext->getDocumentIterator(),
            $options,
        );

        $tocNode =  (new TocNode($toctreeFiles))->withOptions($this->optionsToArray($options));

        if (isset($options['caption'])) {
            $blockContextOfCaption = new BlockContext($blockContext->getDocumentParserContext(), (string) $options['caption']->getValue());
            $inlineNode = $this->startingRule->apply($blockContextOfCaption);
            $tocNode = $tocNode->withCaption($inlineNode);
        }

        if ($directive->getOptionBool('reversed')) {
            $tocNode = $tocNode->withReversed(true);
        }

        return $tocNode;
    }
}
