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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions;

use InvalidArgumentException;
use phpDocumentor\Guides\MarkupLanguageParser;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\Links\Link;
use phpDocumentor\Guides\Nodes\Links\Link as LinkParser;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\LineDataParser;
use phpDocumentor\Guides\RestructuredText\Parser\LinesIterator;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#hyperlink-targets
 */
final class LinkRule implements Rule
{
    public function applies(DocumentParserContext $documentParser): bool
    {
        $link = $this->parseLink($documentParser->getDocumentIterator()->current());

        return $link !== null;
    }

    public function apply(DocumentParserContext $documentParserContext, ?Node $on = null): ?Node
    {
        $documentIterator = $documentParserContext->getDocumentIterator();
        $link = $this->parseLink($documentIterator->current());
        if ($link === null) {
            throw new InvalidArgumentException();
        }

        $node = null;
        if ($link->getType() === LinkParser::TYPE_ANCHOR) {
            $node = new AnchorNode($link->getName());
        }

        $documentParserContext->getContext()->setLink($link->getName(), $link->getUrl());

        return $node;
    }

    private function parseLink(string $line): ?Link
    {
        // Links
        if (preg_match('/^\.\. _`(.+)`: (.+)$/mUsi', $line, $match) > 0) {
            return $this->createLink($match[1], $match[2], Link::TYPE_LINK);
        }

        // anonymous links
        if (preg_match('/^\.\. _(.+): (.+)$/mUsi', $line, $match) > 0) {
            return $this->createLink($match[1], $match[2], Link::TYPE_LINK);
        }

        // Short anonymous links
        if (preg_match('/^__ (.+)$/mUsi', trim($line), $match) > 0) {
            $url = $match[1];

            return $this->createLink('_', $url, Link::TYPE_LINK);
        }

        // Anchor links - ".. _`anchor-link`:"
        if (preg_match('/^\.\. _`(.+)`:$/mUsi', trim($line), $match) > 0) {
            $anchor = $match[1];

            return new Link($anchor, '#' . $anchor, Link::TYPE_ANCHOR);
        }

        if (preg_match('/^\.\. _(.+):$/mUsi', trim($line), $match) > 0) {
            $anchor = $match[1];

            return $this->createLink($anchor, '#' . $anchor, Link::TYPE_ANCHOR);
        }

        return null;
    }

    private function createLink(string $name, string $url, string $type): Link
    {
        return new Link($name, $url, $type);
    }
}
