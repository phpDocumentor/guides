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

use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\LinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

use function dirname;
use function filter_var;
use function preg_match;

use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_URL;

/**
 * Renders an image, example :
 *
 * .. image:: image.jpg
 *      :width: 100
 *      :title: An image
 */
final class ImageDirective extends BaseDirective
{
    /** @see https://regex101.com/r/9dUrzu/3 */
    public const REFERENCE_REGEX = '/^([a-zA-Z0-9-_]+)_$/';

    /** @see https://regex101.com/r/6vPoiA/2 */
    public const REFERENCE_ESCAPED_REGEX = '/^`([^`]+)`_$/';

    public function __construct(
        private readonly DocumentNameResolverInterface $documentNameResolver,
    ) {
    }

    public function getName(): string
    {
        return 'image';
    }

    /** {@inheritDoc} */
    public function processNode(
        BlockContext $blockContext,
        Directive $directive,
    ): Node {
        $node = new ImageNode(
            $this->documentNameResolver->absoluteUrl(
                dirname($blockContext->getDocumentParserContext()->getContext()->getCurrentAbsolutePath()),
                $directive->getData(),
            ),
        );
        if ($directive->hasOption('target')) {
            $targetReference = (string) $directive->getOption('target')->getValue();
            $node->setTarget($this->resolveLinkTarget($targetReference));
        }

        return $node;
    }

    private function resolveLinkTarget(string $targetReference): LinkInlineNode
    {
        if (filter_var($targetReference, FILTER_VALIDATE_EMAIL)) {
            return new HyperLinkNode([], $targetReference);
        }

        if (filter_var($targetReference, FILTER_VALIDATE_URL)) {
            return new HyperLinkNode([], $targetReference);
        }

        if (preg_match(self::REFERENCE_REGEX, $targetReference, $matches)) {
            return new ReferenceNode($matches[1]);
        }

        if (preg_match(self::REFERENCE_ESCAPED_REGEX, $targetReference, $matches)) {
            return new ReferenceNode($matches[1]);
        }

        return new DocReferenceNode($targetReference);
    }
}
