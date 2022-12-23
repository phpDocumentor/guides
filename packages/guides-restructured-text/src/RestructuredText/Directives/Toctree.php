<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TocNode;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;

/**
 * Sphinx based Toctree directive.
 *
 * This directive has an issue, as the related documents are resolved on parse, but during the rendering
 * we are using the {@see Metas} to collect the titles of those documents. There is some step missing in our process
 * which could be resolved by using https://github.com/phpDocumentor/guides/pull/21?
 *
 * @link https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#table-of-contents
 */
class Toctree extends Directive
{
    private ToctreeBuilder $toctreeBuilder;

    public function __construct(ToctreeBuilder $toctreeBuilder)
    {
        $this->toctreeBuilder = $toctreeBuilder;
    }

    public function getName(): string
    {
        return 'toctree';
    }

    /**
     * @param string[] $options
     */
    public function process(
        MarkupLanguageParser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        if ($node === null) {
            return null;
        }

        $environment = $parser->getEnvironment();

        $toctreeFiles = $this->toctreeBuilder->buildToctreeFiles($environment, $node, $options);

        return (new TocNode($toctreeFiles))->withOptions($options);
    }
}
