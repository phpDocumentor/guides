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

namespace phpDocumentor\Guides\NodeRenderers\Html;

use phpDocumentor\Guides\Meta\EntryLegacy;
use phpDocumentor\Guides\Metas;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Nodes\TocNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer;
use phpDocumentor\Guides\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Webmozart\Assert\Assert;

use function count;
use function is_array;
use function ltrim;

class TocNodeRenderer implements NodeRenderer
{
    private Renderer $renderer;
    private UrlGeneratorInterface $urlGenerator;
    private Metas $metas;

    public function __construct(
        Renderer $renderer,
        UrlGeneratorInterface $urlGenerator,
        Metas $metas
    ) {
        $this->renderer = $renderer;
        $this->urlGenerator = $urlGenerator;
        $this->metas = $metas;
    }

    public function render(Node $node, RenderContext $environment): string
    {
        Assert::isInstanceOf($node, TocNode::class);

        if ($node->getOption('hidden', false)) {
            return '';
        }

        $tocItems = [];

        foreach ($node->getFiles() as $file) {
            $metaEntry = $this->metas->get(ltrim($file, '/'));
            if ($metaEntry instanceof EntryLegacy === false) {
                continue;
            }

            $this->buildLevel($environment, $node, $metaEntry, 1, $tocItems);
        }

        return $this->renderer->render(
            'toc.html.twig',
            [
                'node' => $node,
            ]
        );
    }

    /**
     * @param TitleNode[] $titles
     * @param mixed[][] $tocItems
     */
    private function buildLevel(
        RenderContext $environment,
        TocNode       $node,
        EntryLegacy   $metaEntry,
        int           $level,
        array         &$tocItems
    ): void {
        $url = $environment->relativeDocUrl($metaEntry->getFile());
        $title = $metaEntry->getTitle();

//        $tocItem = [
//            'targetId' => $title->getId(),
//            'targetUrl' => $url,
//
//            //TODO: titles can have alternative names,
//            //       https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#table-of-contents
//            'title' => $title->getValueString(),
//            'level' => $level,
//            'children' => [],
//        ];

        //$tocItems[] = $tocItem;

        /*
         * We are constructing a tree here...
         */

        /* TODO: allow rendering of child titles, the entry has a title, which is a document.
                 It may contain files, that we need to lookup in meta's.
                 Or titles at the same level. Which should also be part of the TOC?
        */
        foreach ($metaEntry->getChildren() as $title) {
            //Headings at the same level are inserted on $level.
            if ($title->getLevel() === $metaEntry->getTitle()->getLevel()) {
                $tocItems[] = [
                    'targetId' => $title->getId(),
                    'targetUrl' => $environment->relativeDocUrl($metaEntry->getFile(), $title->getId()),

                    //TODO: titles can have alternative names,
                    //    https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html#table-of-contents
                    'title' => $title->getValueString(),
                    'level' => $level,
                    'children' => [],
                ];
                continue;
            }



            //$this->buildLevel($environment, $node,  $level + 1, $tocItem['children']);

            //TODO: render children until we hit the configured maxdepth
        }
    }

    public function supports(Node $node): bool
    {
        return $node instanceof TocNode;
    }
}
