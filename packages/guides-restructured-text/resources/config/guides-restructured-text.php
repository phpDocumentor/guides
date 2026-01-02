<?php

declare(strict_types=1);

use phpDocumentor\Guides\Graphs\Directives\UmlDirective;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use phpDocumentor\Guides\RestructuredText\Directives\AdmonitionDirective;
use phpDocumentor\Guides\RestructuredText\Directives\AttentionDirective;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective;
use phpDocumentor\Guides\RestructuredText\Directives\BreadcrumbDirective;
use phpDocumentor\Guides\RestructuredText\Directives\CautionDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ClassDirective;
use phpDocumentor\Guides\RestructuredText\Directives\CodeBlockDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ConfigurationBlockDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ConfvalDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ContainerDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ContentsDirective;
use phpDocumentor\Guides\RestructuredText\Directives\CsvTableDirective;
use phpDocumentor\Guides\RestructuredText\Directives\DangerDirective;
use phpDocumentor\Guides\RestructuredText\Directives\DefaultRoleDirective;
use phpDocumentor\Guides\RestructuredText\Directives\DeprecatedDirective;
use phpDocumentor\Guides\RestructuredText\Directives\DocumentBlockDirective;
use phpDocumentor\Guides\RestructuredText\Directives\EpigraphDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ErrorDirective;
use phpDocumentor\Guides\RestructuredText\Directives\FigureDirective;
use phpDocumentor\Guides\RestructuredText\Directives\GeneralDirective;
use phpDocumentor\Guides\RestructuredText\Directives\HighlightDirective;
use phpDocumentor\Guides\RestructuredText\Directives\HighlightsDirective;
use phpDocumentor\Guides\RestructuredText\Directives\HintDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ImageDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ImportantDirective;
use phpDocumentor\Guides\RestructuredText\Directives\IncludeDirective;
use phpDocumentor\Guides\RestructuredText\Directives\IndexDirective;
use phpDocumentor\Guides\RestructuredText\Directives\LaTeXMain;
use phpDocumentor\Guides\RestructuredText\Directives\ListTableDirective;
use phpDocumentor\Guides\RestructuredText\Directives\LiteralincludeDirective;
use phpDocumentor\Guides\RestructuredText\Directives\MathDirective;
use phpDocumentor\Guides\RestructuredText\Directives\MenuDirective;
use phpDocumentor\Guides\RestructuredText\Directives\MetaDirective;
use phpDocumentor\Guides\RestructuredText\Directives\NoteDirective;
use phpDocumentor\Guides\RestructuredText\Directives\OptionDirective;
use phpDocumentor\Guides\RestructuredText\Directives\OptionMapper\CodeNodeOptionMapper;
use phpDocumentor\Guides\RestructuredText\Directives\OptionMapper\DefaultCodeNodeOptionMapper;
use phpDocumentor\Guides\RestructuredText\Directives\PullQuoteDirective;
use phpDocumentor\Guides\RestructuredText\Directives\RawDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ReplaceDirective;
use phpDocumentor\Guides\RestructuredText\Directives\RoleDirective;
use phpDocumentor\Guides\RestructuredText\Directives\SectionauthorDirective;
use phpDocumentor\Guides\RestructuredText\Directives\SeeAlsoDirective;
use phpDocumentor\Guides\RestructuredText\Directives\SidebarDirective;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Directives\TableDirective;
use phpDocumentor\Guides\RestructuredText\Directives\TestLoggerDirective;
use phpDocumentor\Guides\RestructuredText\Directives\TipDirective;
use phpDocumentor\Guides\RestructuredText\Directives\TitleDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ToctreeDirective;
use phpDocumentor\Guides\RestructuredText\Directives\TodoDirective;
use phpDocumentor\Guides\RestructuredText\Directives\VersionAddedDirective;
use phpDocumentor\Guides\RestructuredText\Directives\VersionChangedDirective;
use phpDocumentor\Guides\RestructuredText\Directives\WarningDirective;
use phpDocumentor\Guides\RestructuredText\Directives\YoutubeDirective;
use phpDocumentor\Guides\RestructuredText\MarkupLanguageParser;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContextFactory;
use phpDocumentor\Guides\RestructuredText\Parser\InlineParser;
use phpDocumentor\Guides\RestructuredText\Parser\Interlink\DefaultInterlinkParser;
use phpDocumentor\Guides\RestructuredText\Parser\Interlink\InterlinkParser;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\AnnotationRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\BlockQuoteRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\CommentRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DefinitionListRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DirectiveContentRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DirectiveRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DocumentRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\EnumeratedListRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\AbstractFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\AddressFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\AuthorFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\AuthorsFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\ContactFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\CopyrightFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\DateFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\DedicationFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\FieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\NavigationTitleFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\NocommentsFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\NosearchFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\OrganizationFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\OrphanFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\ProjectFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\RevisionFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\TocDepthFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList\VersionFieldListItemRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldListRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\GridTableRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineMarkupRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\InlineRules\InlineRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\LineBlockRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\LinkRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\ListRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\LiteralBlockRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\ParagraphRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\RuleContainer;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\SectionRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\SimpleTableRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Table\GridTableBuilder;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\TitleRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\TransitionRule;
use phpDocumentor\Guides\RestructuredText\TextRoles\AbbreviationTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\ApiClassTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\DefaultTextRoleFactory;
use phpDocumentor\Guides\RestructuredText\TextRoles\DocReferenceTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericLinkProvider;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericReferenceTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\GenericTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\LiteralTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\MathTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\NbspTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\ReferenceTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\SpanTextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRole;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRoleFactory;
use phpDocumentor\Guides\RestructuredText\Toc\GlobSearcher;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;
use phpDocumentor\Guides\Settings\SettingsManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$bodyElements', service('phpdoc.guides.parser.rst.body_elements'))
        ->bind('$structuralElements', service('phpdoc.guides.parser.rst.structural_elements'))
        ->instanceof(BaseDirective::class)
        ->tag('phpdoc.guides.directive')
        ->instanceof(FieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')
        ->instanceof(InlineRule::class)
        ->tag('phpdoc.guides.parser.rst.inline_rule')
        ->instanceof(TextRole::class)
        ->tag('phpdoc.guides.parser.rst.text_role')
        ->instanceof(SubDirective::class)
        ->bind('$startingRule', service(DirectiveContentRule::class))

        ->load(
            'phpDocumentor\\Guides\RestructuredText\\Parser\\Productions\\InlineRules\\',
            '../../src/RestructuredText/Parser/Productions/InlineRules',
        )
        ->load(
            'phpDocumentor\\Guides\RestructuredText\\NodeRenderers\\Html\\',
            '../../src/RestructuredText/NodeRenderers/Html',
        )
        ->tag('phpdoc.guides.noderenderer.html')
        ->load(
            'phpDocumentor\\Guides\RestructuredText\\NodeRenderers\\LaTeX\\',
            '../../src/RestructuredText/NodeRenderers/LaTeX',
        )
        ->tag('phpdoc.guides.noderenderer.tex')

        ->set(GenericLinkProvider::class)

        ->set(DirectiveContentRule::class)
        ->set(DocReferenceTextRole::class)
        ->set(GenericReferenceTextRole::class)
        ->set(ReferenceTextRole::class)
        ->set(AbbreviationTextRole::class)
        ->set(ApiClassTextRole::class)
        ->set(MathTextRole::class)
        ->set(LiteralTextRole::class)
        ->set(NbspTextRole::class)
        ->set(SpanTextRole::class)

        ->set(GeneralDirective::class)
        ->set(AdmonitionDirective::class)
        ->set(AttentionDirective::class)
        ->set(BreadcrumbDirective::class)
        ->set(CautionDirective::class)
        ->set(ClassDirective::class)
        ->set(CodeBlockDirective::class)
        ->args([
            '$codeNodeOptionMapper' => service(CodeNodeOptionMapper::class),
        ])
        ->set(ConfvalDirective::class)
        ->set(ConfigurationBlockDirective::class)
        ->args([
            '$languageLabels' => param('phpdoc.rst.code_language_labels'),
        ])
        ->set(ContainerDirective::class)
        ->set(ContentsDirective::class)
        ->arg('$documentNameResolver', service(DocumentNameResolverInterface::class))
        ->set(CsvTableDirective::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->set(DangerDirective::class)
        ->set(DefaultRoleDirective::class)
        ->set(DeprecatedDirective::class)
        ->set(DocumentBlockDirective::class)
        ->set(EpigraphDirective::class)
        ->set(ErrorDirective::class)
        ->set(FigureDirective::class)
        ->set(HighlightDirective::class)
        ->set(HighlightsDirective::class)
        ->set(HintDirective::class)
        ->set(ImageDirective::class)
        ->set(ImportantDirective::class)
        ->set(IncludeDirective::class)
        ->arg('$startingRule', service(DocumentRule::class))
        ->set(IndexDirective::class)
        ->set(LaTeXMain::class)
        ->set(ListTableDirective::class)
        ->set(LiteralincludeDirective::class)
        ->args([
            '$codeNodeOptionMapper' => service(
                CodeNodeOptionMapper::class,
            ),
        ])
        ->set(MathDirective::class)
        ->set(MetaDirective::class)
        ->set(NoteDirective::class)
        ->set(OptionDirective::class)
        ->set(PullQuoteDirective::class)
        ->set(RawDirective::class)
        ->set(ReplaceDirective::class)
        ->set(RoleDirective::class)
        ->set(SectionauthorDirective::class)
        ->set(SeeAlsoDirective::class)
        ->set(SidebarDirective::class)
        ->set(TableDirective::class)
        ->set(TestLoggerDirective::class)
        ->set(TipDirective::class)
        ->set(TitleDirective::class)
        ->set(ToctreeDirective::class)
        ->bind('$startingRule', service(InlineMarkupRule::class))
        ->set(MenuDirective::class)
        ->set(TodoDirective::class)
        ->set(UmlDirective::class)
        ->set(VersionAddedDirective::class)
        ->set(VersionChangedDirective::class)
        ->set(WarningDirective::class)
        ->set(YoutubeDirective::class)

        ->set(GenericTextRole::class, GenericTextRole::class)
        ->arg('$settingsManager', inline_service(SettingsManager::class))
        ->set(DefaultTextRoleFactory::class, DefaultTextRoleFactory::class)
        ->arg('$genericTextRole', service(GenericTextRole::class))

        ->arg('$defaultTextRole', inline_service(LiteralTextRole::class))
        ->arg('$textRoles', tagged_iterator('phpdoc.guides.parser.rst.text_role'))
        ->alias(TextRoleFactory::class, DefaultTextRoleFactory::class)

        ->set('phpdoc.guides.parser.rst.body_elements', RuleContainer::class)
        ->set('phpdoc.guides.parser.rst.structural_elements', RuleContainer::class)

        ->set(InterlinkParser::class, DefaultInterlinkParser::class)

        ->set(AnnotationRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => AnnotationRule::PRIORITY])
        ->set(LinkRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => LinkRule::PRIORITY])
        ->set(LiteralBlockRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => LiteralBlockRule::PRIORITY])
        ->set(BlockQuoteRule::class)
        ->arg('$startingRule', service(DirectiveContentRule::class))
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => BlockQuoteRule::PRIORITY])
        ->set(ListRule::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => ListRule::PRIORITY])
        ->set(EnumeratedListRule::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => EnumeratedListRule::PRIORITY])
        ->set(LineBlockRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => ParagraphRule::PRIORITY + 1])
        ->set(DirectiveRule::class)
        ->arg('$directives', tagged_iterator('phpdoc.guides.directive'))
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => DirectiveRule::PRIORITY])
        ->set(CommentRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => CommentRule::PRIORITY])
        ->set(GridTableRule::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => GridTableRule::PRIORITY])
        ->set(GridTableBuilder::class)
        ->set(SimpleTableRule::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => SimpleTableRule::PRIORITY])
        ->set(DefinitionListRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => DefinitionListRule::PRIORITY])
        ->set(FieldListRule::class)
        ->arg('$productions', service('phpdoc.guides.parser.rst.body_elements'))
        ->arg('$fieldListItemRules', tagged_iterator('phpdoc.guides.parser.rst.fieldlist'))
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => FieldListRule::PRIORITY])
        ->set(ParagraphRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => ParagraphRule::PRIORITY])
        ->set(TransitionRule::class)
        ->tag('phpdoc.guides.parser.rst.body_element', ['priority' => TransitionRule::PRIORITY])
        ->set(InlineMarkupRule::class)
        ->set(TitleRule::class)


        ->set(AbstractFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(AddressFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(AuthorFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(AuthorsFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(ContactFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(CopyrightFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(DateFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(DedicationFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(NavigationTitleFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(NocommentsFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(NosearchFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(OrganizationFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(OrphanFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(ProjectFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')
        ->args([
            '$logger' => service(LoggerInterface::class),
        ])

        ->set(RevisionFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(TocDepthFieldListItemRule::class)
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(VersionFieldListItemRule::class)
        ->args([
            '$logger' => service(LoggerInterface::class),
        ])
        ->tag('phpdoc.guides.parser.rst.fieldlist')

        ->set(SectionRule::class)
        ->tag('phpdoc.guides.parser.rst.structural_element', ['priority' => SectionRule::PRIORITY])

        ->set(DocumentParserContextFactory::class)

        ->set(MarkupLanguageParser::class)
        ->args([
            '$startingRule' => service(DocumentRule::class),
        ])
        ->tag('phpdoc.guides.parser.markupLanguageParser')
        ->set(DocumentRule::class)
        ->set(InlineParser::class)
        ->arg('$inlineRules', tagged_iterator('phpdoc.guides.parser.rst.inline_rule'))
        ->set(GlobSearcher::class)
        ->set(ToctreeBuilder::class)
        ->set(InlineMarkupRule::class)
        ->set(DefaultCodeNodeOptionMapper::class)
        ->alias(CodeNodeOptionMapper::class, DefaultCodeNodeOptionMapper::class);
};
