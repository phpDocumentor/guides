<?php

declare(strict_types=1);

use phpDocumentor\Guides\RestructuredText\Directives\AdmonitionDirective;
use phpDocumentor\Guides\RestructuredText\Directives\AttentionDirective;
use phpDocumentor\Guides\RestructuredText\Directives\CautionDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ClassDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Code;
use phpDocumentor\Guides\RestructuredText\Directives\CodeBlock;
use phpDocumentor\Guides\RestructuredText\Directives\ContainerDirective;
use phpDocumentor\Guides\RestructuredText\Directives\DangerDirective;
use phpDocumentor\Guides\RestructuredText\Directives\DeprecatedDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ErrorDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Figure;
use phpDocumentor\Guides\RestructuredText\Directives\HintDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Image;
use phpDocumentor\Guides\RestructuredText\Directives\ImportantDirective;
use phpDocumentor\Guides\RestructuredText\Directives\IncludeDirective;
use phpDocumentor\Guides\RestructuredText\Directives\IndexDirective;
use phpDocumentor\Guides\RestructuredText\Directives\LaTeXMain;
use phpDocumentor\Guides\RestructuredText\Directives\Meta;
use phpDocumentor\Guides\RestructuredText\Directives\NoteDirective;
use phpDocumentor\Guides\RestructuredText\Directives\RawDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Replace;
use phpDocumentor\Guides\RestructuredText\Directives\RoleDirective;
use phpDocumentor\Guides\RestructuredText\Directives\SeeAlsoDirective;
use phpDocumentor\Guides\RestructuredText\Directives\SidebarDirective;
use phpDocumentor\Guides\RestructuredText\Directives\TipDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Title;
use phpDocumentor\Guides\RestructuredText\Directives\Toctree;
use phpDocumentor\Guides\RestructuredText\Directives\TopicDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Uml;
use phpDocumentor\Guides\RestructuredText\Directives\VersionAddedDirective;
use phpDocumentor\Guides\RestructuredText\Directives\VersionChangedDirective;
use phpDocumentor\Guides\RestructuredText\Directives\WarningDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Wrap;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\BlockQuoteRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\CommentRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DefinitionListRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DirectiveRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\EnumeratedListRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\FieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldListRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\GridTableRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkupRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\LinkRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\ListRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\LiteralBlockRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\RuleContainer;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\SectionRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\SimpleTableRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\TitleRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\TransitionRule;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$bodyElements', service('phpdoc.guides.parser.rst.body_elements'))
        ->bind('$structuralElements', service('phpdoc.guides.parser.rst.structural_elements'))
        ->instanceof(phpDocumentor\Guides\RestructuredText\Directives\Directive::class)
        ->tag('phpdoc.guides.directive')
        ->instanceof(FieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')
        ->instanceof(phpDocumentor\Guides\NodeRenderers\NodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->load(
            'phpDocumentor\\Guides\RestructuredText\\NodeRenderers\\Html\\',
            '%vendor_dir%/phpdocumentor/guides-restructured-text/src/RestructuredText/NodeRenderers/Html',
        )

        ->set(AdmonitionDirective::class)
        ->set(AttentionDirective::class)
        ->set(CautionDirective::class)
        ->set(ClassDirective::class)
        ->set(Code::class)
        ->set(CodeBlock::class)
        ->set(ContainerDirective::class)
        ->set(DangerDirective::class)
        ->set(DeprecatedDirective::class)
        ->set(ErrorDirective::class)
        ->set(Figure::class)
        ->set(HintDirective::class)
        ->set(Image::class)
        ->set(ImportantDirective::class)
        ->set(IncludeDirective::class)
        ->set(IndexDirective::class)
        ->set(LaTeXMain::class)
        ->set(Meta::class)
        ->set(NoteDirective::class)
        ->set(RawDirective::class)
        ->set(Replace::class)
        ->set(RoleDirective::class)
        ->set(SeeAlsoDirective::class)
        ->set(SidebarDirective::class)
        ->set(TipDirective::class)
        ->set(Title::class)
        ->set(Toctree::class)
        ->set(TopicDirective::class)
        ->set(Uml::class)
        ->set(VersionAddedDirective::class)
        ->set(VersionChangedDirective::class)
        ->set(WarningDirective::class)
        ->set(Wrap::class)

        ->set('phpdoc.guides.parser.rst.body_elements', RuleContainer::class)
        ->set('phpdoc.guides.parser.rst.structural_elements', RuleContainer::class)

        ->set(LinkRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(LiteralBlockRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(BlockQuoteRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(ListRule::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(EnumeratedListRule::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(DirectiveRule::class)
        ->arg('$directives', tagged_iterator('phpdoc.guides.directive'))
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(CommentRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(GridTableRule::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(SimpleTableRule::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(DefinitionListRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(FieldListRule::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->arg('$fieldListItemRules', tagged_iterator('phpdoc.guides.parser.rst.fieldlist'))
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(ParagraphRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element')
        ->set(InlineMarkupRule::class)
        ->set(TitleRule::class)

        ->set(TransitionRule::class)
        ->tag('phpdoc.guides.parser.rst.structural_element')
        ->set(SectionRule::class)
        ->tag('phpdoc.guides.parser.rst.structural_element')

        ->set(phpDocumentor\Guides\RestructuredText\MarkupLanguageParser::class)
        ->args([
            '$startingRule' => service(
                phpDocumentor\Guides\RestructuredText\Parser\Productions\DocumentRule::class,
            ),
        ])
        ->tag('phpdoc.guides.parser.markupLanguageParser')
        ->set(phpDocumentor\Guides\RestructuredText\Parser\Productions\DocumentRule::class)
        ->set(phpDocumentor\Guides\RestructuredText\Span\SpanParser::class)
        ->set(phpDocumentor\Guides\RestructuredText\Toc\GlobSearcher::class)
        ->set(phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder::class);
};
