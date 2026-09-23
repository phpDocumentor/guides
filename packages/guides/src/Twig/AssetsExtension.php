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

use Doctrine\Deprecations\Deprecation;
use InvalidArgumentException;
use League\Uri\BaseUri;
use League\Uri\Uri;
use phpDocumentor\Guides\Meta\InternalTarget;
use phpDocumentor\Guides\Meta\Target;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\BreadCrumbNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use RuntimeException;
use Stringable;
use Twig\DeprecatedCallableInfo;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;

use function class_exists;
use function func_get_arg;
use function func_num_args;
use function get_debug_type;
use function sprintf;
use function trim;

final class AssetsExtension extends AbstractExtension
{
    private GlobalMenuExtension $menuExtension;
    /** @var NodeRenderer<Node> */
    private NodeRenderer $nodeRenderer;
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @param NodeRenderer<Node>    $nodeRenderer
     * @param UrlGeneratorInterface $urlGenerator
     */
    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    public function __construct($nodeRenderer, $urlGenerator)
    {
        if (func_num_args() > 2) {
            // deprecated signature
            $nodeRenderer = $urlGenerator;
            $urlGenerator = func_get_arg(3);

            Deprecation::trigger(
                'phpdocumentor/guides',
                'https://github.com/phpDocumentor/guides/issues/1389',
                'Passing a LoggerInterface and DocumentNameResolverInterface to the constructor of "%s" is deprecated',
                self::class,
            );
        }

        if (!$nodeRenderer instanceof NodeRenderer) {
            throw new InvalidArgumentException(sprintf(
                'Parameter #1 of "%s" must be an instance of "%s", "%s" given',
                __METHOD__,
                NodeRenderer::class,
                get_debug_type($nodeRenderer),
            ));
        }

        if (!$urlGenerator instanceof UrlGeneratorInterface) {
            throw new InvalidArgumentException(sprintf(
                'Parameter #2 of "%s" must be an instance of "%s", "%s" given',
                __METHOD__,
                UrlGeneratorInterface::class,
                get_debug_type($urlGenerator),
            ));
        }

        $this->nodeRenderer = $nodeRenderer;
        $this->urlGenerator = $urlGenerator;
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
        return $this->urlGenerator->generateInternalUrl($context['env'] ?? null, trim($path, '/'));
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
