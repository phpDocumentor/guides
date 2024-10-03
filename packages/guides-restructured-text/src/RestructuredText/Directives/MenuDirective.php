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

use phpDocumentor\Guides\Nodes\Menu\GlobMenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\NavMenuNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\DirectiveOption;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;

use function count;

/**
 * A menu directives displays a menu in the page. In opposition to a toctree directive the menu
 * is for display only. It does not change the position of document in the document tree and can therefore be included
 * all pages as navigation.
 *
 * By default, it displays a menu of the pages on level 1 up to level 2.
 */
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

    public function getName(): string
    {
        return 'menu';
    }

    /** {@inheritDoc} */
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): Node|null {
        $parserContext = $blockContext->getDocumentParserContext()->getParser()->getParserContext();
        $options = $directive->getOptions();
        $options['glob'] = new DirectiveOption('glob', true);
        $indexName = $this->settingsManager->getProjectSettings()->getIndexName();
        $options['globExclude'] ??= new DirectiveOption('globExclude', $indexName);

        $toctreeFiles = $this->toctreeBuilder->buildToctreeEntries(
            $parserContext,
            $blockContext->getDocumentIterator(),
            $options,
        );
        if (count($toctreeFiles) === 0) {
            $toctreeFiles[] = new GlobMenuEntryNode('/*');
        }

        return (new NavMenuNode($toctreeFiles))->withOptions($this->optionsToArray($options));
    }
}
