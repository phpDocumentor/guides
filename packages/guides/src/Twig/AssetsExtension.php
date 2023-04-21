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

namespace phpDocumentor\Guides\Twig;

use League\Flysystem\Exception;
use LogicException;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;
use Webmozart\Assert\Assert;

use function sprintf;
use function trim;

final class AssetsExtension extends AbstractExtension
{
    /** @param NodeRenderer<Node> $nodeRenderer */
    public function __construct(
        private LoggerInterface $logger,
        private NodeRenderer $nodeRenderer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'asset'], ['is_safe' => ['html'], 'needs_context' => true]),
            new TwigFunction('renderNode', [$this, 'renderNode'], ['is_safe' => ['html'], 'needs_context' => true]),
        ];
    }

    /** @return TwigTest[] */
    public function getTests(): array
    {
        return [
            new TwigTest(
                'node',
                /** @param mixed $value */
                static fn ($value): bool => $value instanceof Node,
            ),
        ];
    }

    /**
     * Copies the referenced asset and returns the canonical path to that asset; thus taking the BASE tag into account.
     *
     * The layout for guides includes a BASE tag in the head, which creates the need for all relative urls to actually
     * be relative not to the current file's path; but the root of the Documentation Set. This means that, when
     * rendering paths, you always need to include the canonical path; not that relative to the current file.
     *
     * @param array{env: RenderContext} $context
     */
    public function asset(array $context, string $path): string
    {
        $outputPath = $this->copyAsset($context['env'] ?? null, $path);

        // make it relative so it plays nice with the base tag in the HEAD
        return trim($outputPath, '/');
    }

    /**
     * @param array{env: RenderContext} $context
     * @param Node|Node[]|null $node
     */
    public function renderNode(array $context, Node|array|null $node): string
    {
        if ($node === null) {
            return '';
        }

        $renderContext = $context['env'] ?? null;
        if (!$renderContext instanceof RenderContext) {
            throw new RuntimeException('Render context must be set in the twig global state to render nodes');
        }

        if ($node instanceof Node) {
            return $this->nodeRenderer->render($node, $renderContext);
        }

        $text = '';
        foreach ($node as $child) {
            $text .= $this->nodeRenderer->render($child, $renderContext);
        }

        return $text;
    }

    private function copyAsset(
        RenderContext|null $renderContext,
        string $sourcePath,
    ): string {
        if (!$renderContext instanceof RenderContext) {
            return $sourcePath;
        }

        $canonicalUrl = $renderContext->canonicalUrl($sourcePath);
        Assert::string($canonicalUrl);
        $outputPath = $this->urlGenerator->absoluteUrl(
            $renderContext->getDestinationPath(),
            $canonicalUrl,
        );

        try {
            if ($renderContext->getOrigin()->has($sourcePath) === false) {
                $this->logger->error(sprintf('Image reference not found "%s"', $sourcePath));

                return $outputPath;
            }

            $fileContents = $renderContext->getOrigin()->read($sourcePath);
            if ($fileContents === false) {
                $this->logger->error(sprintf('Could not read image file "%s"', $sourcePath));

                return $outputPath;
            }

            $result = $renderContext->getDestination()->put($outputPath, $fileContents);
            if ($result === false) {
                $this->logger->error(sprintf('Unable to write file "%s"', $outputPath));
            }
        } catch (LogicException | Exception $e) {
            $this->logger->error(sprintf('Unable to write file "%s", %s', $outputPath, $e->getMessage()));
        }

        return $outputPath;
    }
}
