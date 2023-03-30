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
use League\Flysystem\FilesystemException;
use LogicException;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\UrlGenerator;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;
use Webmozart\Assert\Assert;

use function dirname;
use function sprintf;
use function trim;

final class AssetsExtension extends AbstractExtension
{
    private LoggerInterface $logger;
    /** @var NodeRenderer<Node> */
    private NodeRenderer $nodeRenderer;
    private UrlGenerator $urlGenerator;

    /** @param NodeRenderer<Node> $nodeRenderer */
    public function __construct(
        LoggerInterface $logger,
        NodeRenderer $nodeRenderer,
        UrlGenerator    $urlGenerator
    ) {
        $this->logger = $logger;
        $this->nodeRenderer = $nodeRenderer;
        $this->urlGenerator = $urlGenerator;
    }

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
                fn ($value):bool => $value instanceof Node
            )
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
    public function renderNode(array $context, $node): string
    {
        if ($node === null) {
            return '';
        }

        $environment = $context['env'] ?? null;
        if (!$environment instanceof RenderContext) {
            throw new RuntimeException('Environment must be set in the twig global state to render nodes');
        }

        if ($node instanceof Node) {
            return $this->nodeRenderer->render($node, $environment);
        }

        $text = '';
        foreach ($node as $child) {
            $text .= $this->nodeRenderer->render($child, $environment);
        }

        return $text;
    }

    private function copyAsset(
        ?RenderContext $environment,
        string $sourcePath
    ): string {
        if (!$environment instanceof RenderContext) {
            return $sourcePath;
        }

        $canonicalUrl = $environment->canonicalUrl($sourcePath);
        Assert::string($canonicalUrl);
        $outputPath = $this->urlGenerator->absoluteUrl(
            $environment->getDestinationPath(),
            $canonicalUrl
        );

        try {
            if ($environment->getOrigin()->has($sourcePath) === false) {
                $this->logger->error(sprintf('Image reference not found "%s"', $sourcePath));

                return $outputPath;
            }


            $fileContents = $environment->getOrigin()->read($sourcePath);
            if ($fileContents === false) {
                $this->logger->error(sprintf('Could not read image file "%s"', $sourcePath));

                return $outputPath;
            }

            $result = $environment->getDestination()->put($outputPath, $fileContents);
            if ($result === false) {
                $this->logger->error(sprintf('Unable to write file "%s"', $outputPath));
            }
        } catch (LogicException|Exception $e) {
            $this->logger->error(sprintf('Unable to write file "%s", %s', $outputPath, $e->getMessage()));
        }

        return $outputPath;
    }
}
