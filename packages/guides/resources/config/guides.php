<?php

declare(strict_types=1);

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Compiler\Compiler;
use phpDocumentor\Guides\Compiler\CompilerPass;
use phpDocumentor\Guides\Compiler\DocumentNodeTraverser;
use phpDocumentor\Guides\Compiler\NodeTransformer;
use phpDocumentor\Guides\Compiler\NodeTransformers\CustomNodeTransformerFactory;
use phpDocumentor\Guides\Compiler\NodeTransformers\NodeTransformerFactory;
use phpDocumentor\Guides\Interlink\InventoryLoader;
use phpDocumentor\Guides\Interlink\InventoryRepository;
use phpDocumentor\Guides\Interlink\JsonLoader;
use phpDocumentor\Guides\NodeRenderers\Html\BreadCrumbNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\DocumentNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\GeneralNodeHtmlRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\MenuEntryRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\MenuNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\Html\TableNodeRenderer;
use phpDocumentor\Guides\NodeRenderers\LaTeX\GeneralNodeLatexRenderer;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactoryAware;
use phpDocumentor\Guides\NodeRenderers\OutputAwareDelegatingNodeRenderer;
use phpDocumentor\Guides\Nodes\AnchorNode;
use phpDocumentor\Guides\Nodes\AnnotationListNode;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\DefinitionListNode;
use phpDocumentor\Guides\Nodes\DefinitionLists\DefinitionNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\FieldListNode;
use phpDocumentor\Guides\Nodes\FigureNode;
use phpDocumentor\Guides\Nodes\FootnoteNode;
use phpDocumentor\Guides\Nodes\ImageNode;
use phpDocumentor\Guides\Nodes\Inline\AbbreviationInlineNode;
use phpDocumentor\Guides\Nodes\Inline\CitationInlineNode;
use phpDocumentor\Guides\Nodes\Inline\DocReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\EmphasisInlineNode;
use phpDocumentor\Guides\Nodes\Inline\FootnoteInlineNode;
use phpDocumentor\Guides\Nodes\Inline\GenericTextRoleInlineNode;
use phpDocumentor\Guides\Nodes\Inline\HyperLinkNode;
use phpDocumentor\Guides\Nodes\Inline\ImageInlineNode;
use phpDocumentor\Guides\Nodes\Inline\LiteralInlineNode;
use phpDocumentor\Guides\Nodes\Inline\NewlineInlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;
use phpDocumentor\Guides\Nodes\Inline\ReferenceNode;
use phpDocumentor\Guides\Nodes\Inline\StrongInlineNode;
use phpDocumentor\Guides\Nodes\Inline\VariableInlineNode;
use phpDocumentor\Guides\Nodes\Inline\WhitespaceInlineNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ListItemNode;
use phpDocumentor\Guides\Nodes\ListNode;
use phpDocumentor\Guides\Nodes\LiteralBlockNode;
use phpDocumentor\Guides\Nodes\Metadata\AddressNode;
use phpDocumentor\Guides\Nodes\Metadata\AuthorNode;
use phpDocumentor\Guides\Nodes\Metadata\AuthorsNode;
use phpDocumentor\Guides\Nodes\Metadata\ContactNode;
use phpDocumentor\Guides\Nodes\Metadata\CopyrightNode;
use phpDocumentor\Guides\Nodes\Metadata\DateNode;
use phpDocumentor\Guides\Nodes\Metadata\MetaNode;
use phpDocumentor\Guides\Nodes\Metadata\NoCommentsNode;
use phpDocumentor\Guides\Nodes\Metadata\NoSearchNode;
use phpDocumentor\Guides\Nodes\Metadata\OrganizationNode;
use phpDocumentor\Guides\Nodes\Metadata\OrphanNode;
use phpDocumentor\Guides\Nodes\Metadata\RevisionNode;
use phpDocumentor\Guides\Nodes\Metadata\TocDepthNode;
use phpDocumentor\Guides\Nodes\Metadata\TopicNode;
use phpDocumentor\Guides\Nodes\Metadata\VersionNode;
use phpDocumentor\Guides\Nodes\ParagraphNode;
use phpDocumentor\Guides\Nodes\QuoteNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\SeparatorNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use phpDocumentor\Guides\Parser;
use phpDocumentor\Guides\ReferenceResolvers\AnchorReducer;
use phpDocumentor\Guides\ReferenceResolvers\AnchorReferenceResolver;
use phpDocumentor\Guides\ReferenceResolvers\DelegatingReferenceResolver;
use phpDocumentor\Guides\ReferenceResolvers\DocReferenceResolver;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolver;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\ReferenceResolvers\EmailReferenceResolver;
use phpDocumentor\Guides\ReferenceResolvers\ExternalReferenceResolver;
use phpDocumentor\Guides\ReferenceResolvers\InterlinkReferenceResolver;
use phpDocumentor\Guides\ReferenceResolvers\InternalReferenceResolver;
use phpDocumentor\Guides\ReferenceResolvers\ReferenceResolver;
use phpDocumentor\Guides\ReferenceResolvers\ReferenceResolverPreRender;
use phpDocumentor\Guides\ReferenceResolvers\SluggerAnchorReducer;
use phpDocumentor\Guides\Renderer\HtmlRenderer;
use phpDocumentor\Guides\Renderer\InMemoryRendererFactory;
use phpDocumentor\Guides\Renderer\InterlinkObjectsRenderer;
use phpDocumentor\Guides\Renderer\LatexRenderer;
use phpDocumentor\Guides\Renderer\TypeRendererFactory;
use phpDocumentor\Guides\Renderer\UrlGenerator\AbsoluteUrlGenerator;
use phpDocumentor\Guides\Renderer\UrlGenerator\AbstractUrlGenerator;
use phpDocumentor\Guides\Renderer\UrlGenerator\ConfigurableUrlGenerator;
use phpDocumentor\Guides\Renderer\UrlGenerator\RelativeUrlGenerator;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use phpDocumentor\Guides\Settings\SettingsManager;
use phpDocumentor\Guides\TemplateRenderer;
use phpDocumentor\Guides\Twig\AssetsExtension;
use phpDocumentor\Guides\Twig\EnvironmentBuilder;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use phpDocumentor\Guides\Twig\TwigTemplateRenderer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Loader\FilesystemLoader;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('phpdoc.guides.base_template_paths', [__DIR__ . '/../../../guides/resources/template/html']);

    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()

        ->instanceof(NodeRendererFactoryAware::class)
        ->tag('phpdoc.guides.noderendererfactoryaware')

        ->instanceof(CompilerPass::class)
        ->tag('phpdoc.guides.compiler.passes')

        ->instanceof(NodeTransformer::class)
        ->tag('phpdoc.guides.compiler.nodeTransformers')

        ->instanceof(ReferenceResolver::class)
        ->tag('phpdoc.guides.reference_resolver')

        ->load(
            'phpDocumentor\\Guides\\Compiler\\NodeTransformers\\',
            '%vendor_dir%/phpdocumentor/guides/src/Compiler/NodeTransformers/*Transformer.php',
        )

        ->load(
            'phpDocumentor\\Guides\\Compiler\\Passes\\',
            '%vendor_dir%/phpdocumentor/guides/src/Compiler/Passes/*Pass.php',
        )

        ->load(
            'phpDocumentor\\Guides\\NodeRenderers\\',
            '%vendor_dir%/phpdocumentor/guides/src/NodeRenderers',
        )

        ->set(AbsoluteUrlGenerator::class)
        ->set(RelativeUrlGenerator::class)
        ->set(UrlGeneratorInterface::class, ConfigurableUrlGenerator::class)
        ->set(DocumentNameResolverInterface::class, DocumentNameResolver::class)

        ->set(Parser::class)
        ->arg('$parserStrategies', tagged_iterator('phpdoc.guides.parser.markupLanguageParser'))

        ->set(Compiler::class)
        ->arg('$passes', tagged_iterator('phpdoc.guides.compiler.passes'))

        ->set(NodeTransformerFactory::class, CustomNodeTransformerFactory::class)
        ->arg('$transformers', tagged_iterator('phpdoc.guides.compiler.nodeTransformers'))

        ->set(SettingsManager::class)

        ->set(DocumentNodeTraverser::class)

        ->set(InventoryRepository::class)

        ->set(InventoryLoader::class)

        ->set(JsonLoader::class)


        ->set(HttpClientInterface::class)
        ->factory([HttpClient::class, 'create'])

        ->set(AbstractUrlGenerator::class)

        ->set(ExternalReferenceResolver::class)

        ->set(EmailReferenceResolver::class)

        ->set(AnchorReferenceResolver::class)

        ->set(InternalReferenceResolver::class)

        ->set(DocReferenceResolver::class)

        ->set(InterlinkReferenceResolver::class)

        ->set(DelegatingReferenceResolver::class)
        ->arg('$resolvers', tagged_iterator('phpdoc.guides.reference_resolver', defaultPriorityMethod: 'getPriority'))

        ->set(HtmlRenderer::class)
        ->tag(
            'phpdoc.renderer.typerenderer',
            [
                'noderender_tag' => 'phpdoc.guides.noderenderer.html',
                'format' => 'html',
            ],
        )
        ->args(
            ['$commandBus' => service(CommandBus::class)],
        )
        ->set(LatexRenderer::class)
        ->tag(
            'phpdoc.renderer.typerenderer',
            [
                'noderender_tag' => 'phpdoc.guides.noderenderer.tex',
                'format' => 'tex',
            ],
        )

        ->set(InterlinkObjectsRenderer::class)
        ->tag(
            'phpdoc.renderer.typerenderer',
            ['format' => 'interlink'],
        )

        ->set(DocumentNodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->set(TableNodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->set(MenuNodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->set(MenuEntryRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->set(BreadCrumbNodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->set(GeneralNodeHtmlRenderer::class)
        ->arg('$templateMatching', [
            AnchorNode::class => 'inline/anchor.html.twig',
            FigureNode::class => 'body/figure.html.twig',
            MetaNode::class => 'structure/header/meta.html.twig',
            ParagraphNode::class => 'body/paragraph.html.twig',
            QuoteNode::class => 'body/quote.html.twig',
            SeparatorNode::class => 'body/separator.html.twig',
            TitleNode::class => 'structure/header-title.html.twig',
            SectionNode::class => 'structure/section.html.twig',
            DocumentNode::class => 'structure/document.html.twig',
            ImageNode::class => 'body/image.html.twig',
            CodeNode::class => 'body/code.html.twig',
            DefinitionListNode::class => 'body/definition-list.html.twig',
            DefinitionNode::class => 'body/definition.html.twig',
            FieldListNode::class => 'body/field-list.html.twig',
            ListNode::class => 'body/list/list.html.twig',
            ListItemNode::class => 'body/list/list-item.html.twig',
            LiteralBlockNode::class => 'body/literal-block.html.twig',
            CitationNode::class => 'body/citation.html.twig',
            FootnoteNode::class => 'body/footnote.html.twig',
            AnnotationListNode::class => 'body/annotation-list.html.twig',
                // Inline
            ImageInlineNode::class => 'inline/image.html.twig',
            InlineCompoundNode::class => 'inline/inline-node.html.twig',
            AbbreviationInlineNode::class => 'inline/textroles/abbreviation.html.twig',
            CitationInlineNode::class => 'inline/citation.html.twig',
            DocReferenceNode::class => 'inline/doc.html.twig',
            EmphasisInlineNode::class => 'inline/emphasis.html.twig',
            FootnoteInlineNode::class => 'inline/footnote.html.twig',
            HyperLinkNode::class => 'inline/link.html.twig',
            LiteralInlineNode::class => 'inline/literal.html.twig',
            NewlineInlineNode::class => 'inline/newline.html.twig',
            WhitespaceInlineNode::class => 'inline/nbsp.html.twig',
            PlainTextInlineNode::class => 'inline/plain-text.html.twig',
            ReferenceNode::class => 'inline/ref.html.twig',
            StrongInlineNode::class => 'inline/strong.html.twig',
            VariableInlineNode::class => 'inline/variable.html.twig',
            GenericTextRoleInlineNode::class => 'inline/textroles/generic.html.twig',
                // Output as Metatags
            AuthorNode::class => 'structure/header/author.html.twig',
            CopyrightNode::class => 'structure/header/copyright.html.twig',
            DateNode::class => 'structure/header/date.html.twig',
            NoSearchNode::class => 'structure/header/no-search.html.twig',
            TopicNode::class => 'structure/header/topic.html.twig',
                // No output in page header in HTML - might be output in i.e. LaTex
            AddressNode::class => 'structure/header/blank.html.twig',
            AuthorsNode::class => 'structure/header/blank.html.twig',
            ContactNode::class => 'structure/header/blank.html.twig',
            NoCommentsNode::class => 'structure/header/blank.html.twig',
            OrganizationNode::class => 'structure/header/blank.html.twig',
            OrphanNode::class => 'structure/header/blank.html.twig',
            RevisionNode::class => 'structure/header/blank.html.twig',
            TocDepthNode::class => 'structure/header/blank.html.twig',
            VersionNode::class => 'structure/header/blank.html.twig',
        ])
        ->tag('phpdoc.guides.noderenderer.html')

        ->set(id: GeneralNodeLatexRenderer::class)
        ->arg('$templateMatching', [])
        ->tag('phpdoc.guides.noderenderer.tex')

        ->set(ReferenceResolverPreRender::class)
        ->tag('phpdoc.guides.prerenderer')

        ->set(InMemoryRendererFactory::class)
        ->arg('$renderSets', tagged_iterator('phpdoc.renderer.typerenderer', 'format'))
        ->alias(TypeRendererFactory::class, InMemoryRendererFactory::class)

        ->set(SluggerAnchorReducer::class)
        ->alias(AnchorReducer::class, SluggerAnchorReducer::class)

        ->set('phpdoc.guides.output_node_renderer', OutputAwareDelegatingNodeRenderer::class)
        ->arg('$nodeRenderers', tagged_iterator('phpdoc.guides.output_node_renderer', 'format'))

        ->set(AssetsExtension::class)
        ->arg('$nodeRenderer', service('phpdoc.guides.output_node_renderer'))
        ->tag('twig.extension')
        ->autowire()

        ->set(ThemeManager::class)
        ->arg('$filesystemLoader', service(FilesystemLoader::class))
        ->arg(
            '$defaultPaths',
            param('phpdoc.guides.base_template_paths'),
        )

        ->set(FilesystemLoader::class)
        ->arg(
            '$paths',
            param('phpdoc.guides.base_template_paths'),
        )

        ->set(EnvironmentBuilder::class)
        ->arg('$extensions', tagged_iterator('twig.extension'))
        ->arg('$themeManager', service(ThemeManager::class))

        ->set(TemplateRenderer::class, TwigTemplateRenderer::class)
        ->arg('$environmentBuilder', new Reference(EnvironmentBuilder::class));
};
