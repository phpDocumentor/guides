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
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\CompoundNode;
use phpDocumentor\Guides\Nodes\Links\Link as LinkParser;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;

use function preg_match;
use function trim;

/**
 * @link https://docutils.sourceforge.io/docs/ref/rst/restructuredtext.html#hyperlink-targets
 *
 * @implements Rule<AnchorNode>
 */
final class LinkRule implements Rule
{
    public function applies(DocumentParserContext $documentParser): bool
    {
        $link = $this->parseLink($documentParser->getDocumentIterator()->current());

        return $link !== null;
    }

    public function apply(DocumentParserContext $documentParserContext, CompoundNode|null $on = null): Node|null
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

        //TODO: pass link object to setLink
        $documentParserContext->getContext()->setLink($link->getName(), $link->getUrl());

        return $node;
    }

    private function parseLink(string $line): LinkParser|null
    {
        // Links
        if (preg_match('/^\.\. _`(.+)`: (.+)$/mUsi', $line, $match) > 0) {
            return $this->createLink($match[1], $match[2], LinkParser::TYPE_LINK);
        }

        // anonymous links
        if (preg_match('/^\.\. _(.+): (.+)$/mUsi', $line, $match) > 0) {
            return $this->createLink($match[1], $match[2], LinkParser::TYPE_LINK);
        }

        // Short anonymous links
        if (preg_match('/^__ (.+)$/mUsi', trim($line), $match) > 0) {
            $url = $match[1];

            return $this->createLink('_', $url, LinkParser::TYPE_LINK);
        }

        // Anchor links - ".. _`anchor-link`:"
        if (preg_match('/^\.\. _`(.+)`:$/mUsi', trim($line), $match) > 0) {
            $anchor = $match[1];

            return new LinkParser($anchor, '#' . $anchor, LinkParser::TYPE_ANCHOR);
        }

        if (preg_match('/^\.\. _(.+):$/mUsi', trim($line), $match) > 0) {
            $anchor = $match[1];

            return $this->createLink($anchor, '#' . $anchor, LinkParser::TYPE_ANCHOR);
        }

        return null;
    }

    private function createLink(string $name, string $url, string $type): LinkParser
    {
        return new LinkParser($name, $url, $type);
    }
}
