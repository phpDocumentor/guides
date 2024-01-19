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

namespace phpDocumentor\Guides\Markdown;

use League\CommonMark\Node\Node as CommonMarkNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser as GuidesParser;
use phpDocumentor\Guides\Nodes\Node;

/** @template-covariant TValue as Node */
interface ParserInterface
{
    /** @return TValue */
    public function parse(GuidesParser $parser, NodeWalker $walker, CommonMarkNode $current): Node;

    public function supports(NodeWalkerEvent $event): bool;
}
