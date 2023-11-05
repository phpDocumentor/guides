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

use phpDocumentor\Guides\NodeRenderers\Html\GeneralNodeHtmlRenderer;
use phpDocumentor\Guides\RestructuredText\Nodes\VersionChangeNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;

class VersionChangedDirective extends AbstractVersionChangeDirective
{
    public function __construct(
        protected Rule $startingRule,
        GeneralNodeHtmlRenderer $generalNodeRenderer,
    ) {
        parent::__construct($startingRule, 'versionchanged', 'Changed in version %s');

        $generalNodeRenderer->registerNode(VersionChangeNode::class, 'body/version-change.html.twig');
    }
}
