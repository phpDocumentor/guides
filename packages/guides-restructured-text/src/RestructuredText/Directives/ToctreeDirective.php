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
use phpDocumentor\Guides\Nodes\Menu\TocNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Directive;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Directive as ParsedDirective;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;

use function is_scalar;

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
#[Directive(name: 'toctree')]
#[Option(name: 'caption', type: OptionType::InlineMarkup, description: 'Caption to be displayed above the table of contents.')]
#[Option(name: 'depth', type: OptionType::Integer, description: 'Maximum depth of the menu to display.')]
#[Option(name: 'maxdepth', type: OptionType::Integer, description: 'Synonym for depth; depth takes precedence if both are set.')]
#[Option(name: 'glob', type: OptionType::Boolean, default: false, description: 'If true, glob patterns containing stars are considered.')]
#[Option(name: 'globExclude', description: 'Comma-separated paths to exclude from glob patterns.')]
#[Option(name: 'hidden', type: OptionType::Boolean, default: false, description: 'Do not display the menu within the content.')]
#[Option(name: 'reversed', type: OptionType::Boolean, default: false, description: 'Display documents in reversed order.')]
#[Option(name: 'titlesonly', type: OptionType::Boolean, default: false, description: 'Display only page titles.')]
final class ToctreeDirective extends BaseDirective
{
    private SettingsManager $settingsManager;

    public function __construct(
        private readonly ToctreeBuilder $toctreeBuilder,
        SettingsManager|null $settingsManager = null,
    ) {
        // if for backward compatibility reasons no settings manager was passed, use the defaults
        $this->settingsManager = $settingsManager ?? new SettingsManager(new ProjectSettings());
    }

    public function createNode(DirectiveNode $directiveNode, CompilerContextInterface $compilerContext): Node
    {
        $directive = $directiveNode->getDirective();
        $indexName = $this->settingsManager->getProjectSettings()->getIndexName();
        if (!$directive->hasOption('globExclude')) {
            $directive->addOption(new DirectiveOption('globExclude', $indexName));
        }

        $options = $directive->getOptions();

        $toctreeFiles = $this->toctreeBuilder->buildToctreeEntries(
            $this->createLinesIterator($directiveNode->getRawContent()),
            $options,
        );

        $tocNode = (new TocNode($toctreeFiles))->withOptions($this->readProvidedOptions($directive));

        $caption = $this->readOption($directive, 'caption');
        if ($caption instanceof InlineCompoundNode) {
            $tocNode = $tocNode->withCaption($caption);
        }

        if ((bool) $this->readOption($directive, 'reversed')) {
            $tocNode = $tocNode->withReversed(true);
        }

        return $tocNode;
    }

    private function createLinesIterator(string $rawContent): LinesIterator
    {
        $lines = new LinesIterator();
        $lines->load($rawContent, true);

        return $lines;
    }

    /** @return array<string, scalar|scalar[]|null> */
    private function readProvidedOptions(ParsedDirective $directive): array
    {
        $options = [];
        foreach ($directive->getOptions() as $option) {
            if ($option->getName() === 'caption') {
                continue;
            }

            $value = $this->readOption($directive, $option->getName());
            if (!is_scalar($value) && $value !== null) {
                continue;
            }

            $options[$option->getName()] = $value;
        }

        return $options;
    }
}
