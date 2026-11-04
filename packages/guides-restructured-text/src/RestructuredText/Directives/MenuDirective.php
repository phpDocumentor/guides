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
use phpDocumentor\Guides\Nodes\Menu\GlobMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
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

use function count;
use function is_scalar;

/**
 * A menu directives displays a menu in the page. In opposition to a toctree directive the menu
 * is for display only. It does not change the position of document in the document tree and can therefore be included
 * all pages as navigation.
 *
 * By default, it displays a menu of the pages on level 1 up to level 2.
 */
#[Directive(name: 'menu')]
#[Option(name: 'depth', type: OptionType::Integer, description: 'Maximum depth of the menu to display.')]
#[Option(name: 'maxdepth', type: OptionType::Integer, description: 'Synonym for depth.')]
#[Option(name: 'glob', type: OptionType::Boolean, description: 'Treat menu entries as glob patterns.')]
#[Option(name: 'globExclude', description: 'Comma-separated paths to exclude from glob patterns.')]
#[Option(name: 'titlesonly', type: OptionType::Boolean, description: 'Display only page titles.')]
final class MenuDirective extends BaseDirective
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
        $directive->addOption(new DirectiveOption('glob', true));
        $indexName = $this->settingsManager->getProjectSettings()->getIndexName();
        if (!$directive->hasOption('globExclude')) {
            $directive->addOption(new DirectiveOption('globExclude', $indexName));
        }

        $toctreeFiles = $this->toctreeBuilder->buildToctreeEntries(
            $this->createLinesIterator($directiveNode->getRawContent()),
            $directive->getOptions(),
        );
        if (count($toctreeFiles) === 0) {
            $toctreeFiles[] = new GlobMenuEntryNode('/*');
        }

        return (new NavMenuNode($toctreeFiles))->withOptions($this->readProvidedOptions($directive));
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
            $value = $this->readOption($directive, $option->getName());
            if (!is_scalar($value) && $value !== null) {
                continue;
            }

            $options[$option->getName()] = $value;
        }

        return $options;
    }
}
