<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\NodeRenderers\Html;

use Monolog\Logger;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\RestructuredText\Nodes\GeneralDirectiveNode;
use phpDocumentor\Guides\RestructuredText\TextRoles\DefaultTextRoleFactory;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\LiteralTextRole;
use phpDocumentor\Guides\TemplateRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GeneralDirectiveNodeRendererTest extends TestCase
{
    private GeneralDirectiveNodeRenderer $generalDirectiveNodeRenderer;
    private RenderContext&MockObject $renderContext;
    private TemplateRenderer&MockObject $renderer;

    public function setUp(): void
    {
        $this->renderContext = $this->createMock(RenderContext::class);
        $this->renderer = $this->createMock(TemplateRenderer::class);
        $this->generalDirectiveNodeRenderer = new GeneralDirectiveNodeRenderer(
            $this->renderer,
            new Logger('test'),
        );
        $textRoleFactory = new DefaultTextRoleFactory(
            new GenericTextRole(),
            new LiteralTextRole(),
            [],
        );
    }

    #[DataProvider('templateProvider')]
    public function testTemplateFallback(string $directiveName, string $templateName): void
    {
        $this->renderer->expects(self::once())
            ->method('isTemplateFound')
            ->with($this->renderContext, 'body/directive/' . $templateName)
            ->willReturn(true);
        $this->generalDirectiveNodeRenderer->render(
            new GeneralDirectiveNode($directiveName, '', new InlineCompoundNode([])),
            $this->renderContext,
        );
    }

    /** @return array<string, string[]> */
    public static function templateProvider(): array
    {
        return [
            'simple name' => ['name', 'name.html.twig'],
            'with minus' => ['some-name', 'some-name.html.twig'],
            'with space' => ['some name', 'some_name.html.twig'],
            'with special signs' => ['some\\name', 'some_name.html.twig'],
            'with namespace' => ['some:name', 'some/name.html.twig'],
        ];
    }
}
