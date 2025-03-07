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

use League\Uri\BaseUri;
use League\Uri\Uri;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Meta\Target;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\BreadCrumbNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Stringable;
use Throwable;
use Twig\DeprecatedCallableInfo;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

use function class_exists;
use function sprintf;
use function trim;

final class AssetsExtension extends AbstractExtension
{
    private GlobalMenuExtension $menuExtension;

    /** @param NodeRenderer<Node> $nodeRenderer */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly NodeRenderer $nodeRenderer,
        private readonly DocumentNameResolverInterface $documentNameResolver,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        $this->menuExtension = new GlobalMenuExtension($this->nodeRenderer);
    }

    /** @return TwigFunction[] */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', $this->asset(...), ['is_safe' => ['html'], 'needs_context' => true]),
            new TwigFunction('renderNode', $this->renderNode(...), ['is_safe' => ['html'], 'needs_context' => true]),
            new TwigFunction('renderLink', $this->renderLink(...), ['is_safe' => ['html'], 'needs_context' => true]),
            new TwigFunction('renderBreadcrumb', $this->renderBreadcrumb(...), ['is_safe' => ['html'], 'needs_context' => true]),
            new TwigFunction(
                'renderMenu',
                $this->renderMenu(...),
                ['is_safe' => ['html'], 'needs_context' => true] + (class_exists(DeprecatedCallableInfo::class) ? ['deprecation_info' => new DeprecatedCallableInfo('phpdocumentor/guides', '1.1.0', 'renderMenu" from "' . GlobalMenuExtension::class)] : ['deprecated' => true]),
            ),
            new TwigFunction('renderTarget', $this->renderTarget(...), ['is_safe' => ['html'], 'needs_context' => true]),
            new TwigFunction('renderOrderedListType', $this->renderOrderedListType(...), ['is_safe' => ['html'], 'needs_context' => false]),
        ];
    }

    /** @return TwigTest[] */
    public function getTests(): array
    {
        return [
            new TwigTest(
                'node',
                /** @param mixed $value */
                static fn (mixed $value): bool => $value instanceof Node,
            ),
            new TwigTest(
                'external_target',
                static fn (string|Stringable $value): bool => BaseUri::from(Uri::new($value))->isAbsolute(),
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

        return $this->urlGenerator->generateInternalUrl($context['env'] ?? null, trim($outputPath, '/'));
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

        $renderContext = $this->getRenderContext($context);

        if ($node instanceof Node) {
            return $this->nodeRenderer->render($node, $renderContext);
        }

        $text = '';
        foreach ($node as $child) {
            $text .= $this->nodeRenderer->render($child, $renderContext);
        }

        return $text;
    }

    /** @param array{env: RenderContext} $context */
    public function renderTarget(array $context, Target $target): string
    {
        if ($target instanceof InternalTarget) {
            return $this->urlGenerator->generateCanonicalOutputUrl($this->getRenderContext($context), $target->getDocumentPath(), $target->getAnchor());
        }

        return $target->getUrl();
    }

    /** @param array{env: RenderContext} $context */
    public function renderBreadcrumb(array $context): string
    {
        return $this->nodeRenderer->render(new BreadCrumbNode(), $this->getRenderContext($context));
    }

    /** @param array{env: RenderContext} $context */
    public function renderMenu(array $context, string $menuType, int $maxMenuCount = 0): string
    {
        return $this->menuExtension->renderMenu($context, $menuType);
    }

    /** @param array{env: RenderContext} $context */
    public function renderLink(array $context, string $url, string|null $anchor = null): string
    {
        return $this->urlGenerator->generateCanonicalOutputUrl($this->getRenderContext($context), $url, $anchor);
    }

    private function copyAsset(
        RenderContext|null $renderContext,
        string $sourcePath,
    ): string {
        if (!$renderContext instanceof RenderContext) {
            return $sourcePath;
        }

        $canonicalUrl = $this->documentNameResolver->canonicalUrl($renderContext->getDirName(), $sourcePath);
        $normalizedSourcePath = $this->documentNameResolver->canonicalUrl($renderContext->getDirName(), $sourcePath);

        $outputPath = $this->documentNameResolver->absoluteUrl(
            $renderContext->getDestinationPath(),
            $canonicalUrl,
        );

        try {
            if ($renderContext->getOrigin()->has($normalizedSourcePath) === false) {
                $this->logger->error(
                    sprintf('Image reference not found "%s"', $normalizedSourcePath),
                    $renderContext->getLoggerInformation(),
                );

                return $outputPath;
            }

            $fileContents = $renderContext->getOrigin()->read($normalizedSourcePath);
            if ($fileContents === false) {
                $this->logger->error(
                    sprintf('Could not read image file "%s"', $normalizedSourcePath),
                    $renderContext->getLoggerInformation(),
                );

                return $outputPath;
            }

            $result = $renderContext->getDestination()->put($outputPath, $fileContents);
            if ($result === false) {
                $this->logger->error(
                    sprintf('Unable to write file "%s"', $outputPath),
                    $renderContext->getLoggerInformation(),
                );
            }
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf('Unable to write file "%s", %s', $outputPath, $e->getMessage()),
                $renderContext->getLoggerInformation(),
            );
        }

        return $outputPath;
    }

    /** @param array{env: RenderContext} $context */
    private function getRenderContext(array $context): RenderContext
    {
        $renderContext = $context['env'] ?? null;
        if (!$renderContext instanceof RenderContext) {
            throw new RuntimeException('Render context must be set in the twig global state to render nodes');
        }

        return $renderContext;
    }

    public function renderOrderedListType(string $listType): string
    {
        switch ($listType) {
            case 'numberdot':
            case 'numberparentheses':
            case 'numberright-parenthesis':
                return '1';

            case 'romandot':
            case 'romanparentheses':
            case 'romanright-parenthesis':
                return 'i';

            default:
                return 'a';
        }
    }
}
