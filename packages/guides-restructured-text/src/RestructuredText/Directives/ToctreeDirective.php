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

use Doctrine\Deprecations\Deprecation;
use phpDocumentor\Guides\Compiler\CompilerContextInterface;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Directive as DirectiveAttribute;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
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
#[DirectiveAttribute(name: 'toctree', rawContent: true)]
#[Option(name: 'caption', type: OptionType::InlineMarkup)]
#[Option(name: 'depth', type: OptionType::Integer)]
#[Option(name: 'glob', type: OptionType::Boolean)]
#[Option(name: 'globExclude')]
#[Option(name: 'hidden', type: OptionType::Boolean)]
#[Option(name: 'reversed', type: OptionType::Boolean)]
#[Option(name: 'titlesonly', type: OptionType::Boolean)]
#[Option(name: 'maxdepth', type: OptionType::Integer)]
final class ToctreeDirective extends BaseDirective
{
    private SettingsManager $settingsManager;

    /** @param Rule<InlineCompoundNode>|SettingsManager $startingRule */
    public function __construct(
        private readonly ToctreeBuilder $toctreeBuilder,
        Rule|SettingsManager $startingRule,
        SettingsManager|null $settingsManager = null,
    ) {
        if ($startingRule instanceof Rule) {
            Deprecation::trigger(
                'phpdocumentor/guide-restructured-text',
                'https://github.com/phpDocumentor/guides/issues/1373',
                'Passing a Rule to ToctreeDirective is deprecated; pass a SettingsManager instead.',
            );
        } else {
            $settingsManager ??= $startingRule;
        }

        $this->settingsManager = $settingsManager ?? new SettingsManager(new ProjectSettings());
    }

    public function createNode(DirectiveNode $directiveNode, CompilerContextInterface $compilerContext): Node
    {
        $directive = $directiveNode->getDirective();
        $options = $directive->getOptions();
        $indexName = $this->settingsManager->getProjectSettings()->getIndexName();
        $options['globExclude'] ??= new DirectiveOption('globExclude', $indexName);

        $lines = new LinesIterator();
        $lines->load($directiveNode->getRawContent(), true);
        $toctreeFiles = $this->toctreeBuilder->buildToctreeEntriesFromLines($lines, $options);

        $tocNode = (new TocNode($toctreeFiles))->withOptions($this->optionsToArray($options));
        $caption = $this->readOption($directive, 'caption');
        if ($caption instanceof InlineCompoundNode) {
            $tocNode = $tocNode->withCaption($caption);
        }

        if ($this->readOption($directive, 'reversed') === true) {
            $tocNode = $tocNode->withReversed(true);
        }

        return $tocNode;
    }
}
