<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TocNode;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;

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
