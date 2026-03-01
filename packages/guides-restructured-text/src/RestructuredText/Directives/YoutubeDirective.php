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

use phpDocumentor\Guides\Nodes\EmbeddedFrame;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;

/**
 * This directive is used to embed a youtube video in the document.
 *
 * Basic usage
 *
 * ```rst
 *   .. youtube:: dQw4w9WgXcQ
 * ```
 *
 * Options:
 *
 * - string title The title of the video
 * - int width The width of the video, default is 560
 * - int height The height of the video, default is 315
 * - string allow The allow attribute of the iframe, default is 'encrypted-media; picture-in-picture; web-share'
 * - bool allowfullscreen Whether the video should be allowed to go fullscreen, default is true
 */
#[Attributes\Directive(name: 'youtube')]
#[Option('width', type: OptionType::Integer, default: 560, description: 'Width of the video')]
#[Option('title', type: OptionType::String, description: 'Title of the video')]
#[Option('height', type: OptionType::Integer, default: 315, description: 'Height of the video')]
#[Option('allow', type: OptionType::String, default: 'encrypted-media; picture-in-picture; web-share', description: 'Allow attribute of the iframe')]
#[Option('allowfullscreen', type: OptionType::Boolean, default: true, description: 'Whether the video should be allowed to go fullscreen')]
final class YoutubeDirective extends BaseDirective
{
    public function process(
        BlockContext $blockContext,
        Directive $directive,
    ): EmbeddedFrame {
        return $this->createNode(new DirectiveNode($directive));
    }

    public function createNode(DirectiveNode $directiveNode): EmbeddedFrame
    {
        $node = new EmbeddedFrame(
            'https://www.youtube-nocookie.com/embed/' . $directiveNode->getDirective()->getData(),
        );

        return $node->withOptions($this->readAllOptions($directiveNode->getDirective()));
    }
}
