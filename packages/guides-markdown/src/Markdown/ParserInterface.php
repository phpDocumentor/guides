<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Markdown;

use phpDocumentor\Guides\Nodes\CompoundNode;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Node\NodeWalkerEvent;
use phpDocumentor\Guides\MarkupLanguageParser as GuidesParser;

interface ParserInterface
{
    public function parse(GuidesParser $parser, NodeWalker $walker): CompoundNode;

    public function supports(NodeWalkerEvent $event): bool;
}
