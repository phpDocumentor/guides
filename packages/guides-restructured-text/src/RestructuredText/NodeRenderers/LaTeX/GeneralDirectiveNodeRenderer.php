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

namespace phpDocumentor\Guides\RestructuredText\NodeRenderers\LaTeX;

use InvalidArgumentException;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;
use phpDocumentor\Guides\TemplateRenderer;
use Psr\Log\LoggerInterface;

use function is_a;
use function preg_replace;
use function sprintf;
use function str_replace;

/** @implements NodeRenderer<GeneralDirectiveNode> */
final class GeneralDirectiveNodeRenderer implements NodeRenderer
{
    public function __construct(
        private readonly TemplateRenderer $renderer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === GeneralDirectiveNode::class || is_a($nodeFqcn, GeneralDirectiveNode::class, true);
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if ($node instanceof GeneralDirectiveNode === false) {
            throw new InvalidArgumentException('Node must be an instance of ' . GeneralDirectiveNode::class);
        }

        $template = 'body/directive/' . $this->getTemplateName($node->getName()) . '.tex.twig';
        $data = ['node' => $node];
        if ($this->renderer->isTemplateFound($renderContext, $template)) {
            return $this->renderer->renderTemplate($renderContext, $template, $data);
        }

        $this->logger->warning(sprintf(
            'No template found for rendering directive "%s". Expected template "%s"',
            $node->getName(),
            $template,
        ), $renderContext->getLoggerInformation());
        $template = 'body/directive/not-found.html.twig';

        return $this->renderer->renderTemplate($renderContext, $template, $data);
    }
    
    private function getTemplateName(string $directiveName): string
    {
        $directiveName = str_replace(':', '/', $directiveName);
        $directiveName = preg_replace('/[^a-zA-Z0-9-_\/]/', '_', $directiveName);

        return '' . $directiveName;
    }
}
