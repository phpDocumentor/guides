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

use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Nodes\AdmonitionNode;

use function preg_replace;
use function strtolower;
use function trim;

/**
 * A generic admonition.
 *
 * This node allows defining new admonitions from reStructuredText files:
 *
 *     .. admonition:: Screencast
 *
 *         Watch this document in video.
 *
 * @see https://docutils.sourceforge.io/docs/ref/rst/directives.html#generic-admonition
 */
class AdmonitionDirective extends SubDirective
{
    public function getName(): string
    {
        return 'admonition';
    }

    /** {@inheritDoc} */
    protected function processSub(DocumentNode $document, string $variable, string $data, array $options): Node|null
    {
        $name = trim(preg_replace('/[^0-9a-zA-Z]+/', '-', strtolower($data)) ?? '', '-');

        return (new AdmonitionNode(
            $name,
            $data,
            $document->getChildren(),
        ))->withOptions($this->optionsToArray($options));
    }
}
