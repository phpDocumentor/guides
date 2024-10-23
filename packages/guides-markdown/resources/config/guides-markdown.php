<?php

declare(strict_types=1);

use phpDocumentor\Guides\Markdown\MarkupLanguageParser;
use phpDocumentor\Guides\Markdown\Parsers\BlockQuoteParser;
use phpDocumentor\Guides\Markdown\Parsers\CodeBlockParser;
use phpDocumentor\Guides\Markdown\Parsers\FrontMatter\TitleParser;
use phpDocumentor\Guides\Markdown\Parsers\FrontMatterParser;
use phpDocumentor\Guides\Markdown\Parsers\HeaderParser;
use phpDocumentor\Guides\Markdown\Parsers\HtmlParser;
use phpDocumentor\Guides\Markdown\Parsers\InlineParsers\EmphasisParser;
use phpDocumentor\Guides\Markdown\Parsers\InlineParsers\InlineCodeParser;
use phpDocumentor\Guides\Markdown\Parsers\InlineParsers\InlineImageParser;
use phpDocumentor\Guides\Markdown\Parsers\InlineParsers\LinkParser;
use phpDocumentor\Guides\Markdown\Parsers\InlineParsers\NewLineParser;
use phpDocumentor\Guides\Markdown\Parsers\InlineParsers\PlainTextParser;
use phpDocumentor\Guides\Markdown\Parsers\InlineParsers\StrongParser;
use phpDocumentor\Guides\Markdown\Parsers\ListBlockParser;
use phpDocumentor\Guides\Markdown\Parsers\ListItemParser;
use phpDocumentor\Guides\Markdown\Parsers\ParagraphParser;
use phpDocumentor\Guides\Markdown\Parsers\SeparatorParser;
use phpDocumentor\Guides\Markdown\Parsers\Table\TableParser;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\String\Slugger\AsciiSlugger;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()

        ->set(AsciiSlugger::class)

        ->set(HeaderParser::class)
        ->arg('$inlineParsers', tagged_iterator('phpdoc.guides.markdown.parser.inlineParser'))
        ->tag('phpdoc.guides.markdown.parser.blockParser')
        ->set(BlockQuoteParser::class)
        ->arg('$subParsers', tagged_iterator('phpdoc.guides.markdown.parser.subParser'))
        ->tag('phpdoc.guides.markdown.parser.blockParser')
        ->tag('phpdoc.guides.markdown.parser.subParser')
        ->set(HtmlParser::class)
        ->tag('phpdoc.guides.markdown.parser.blockParser')
        ->tag('phpdoc.guides.markdown.parser.subParser')
        ->set(ListBlockParser::class)
        ->tag('phpdoc.guides.markdown.parser.blockParser')
        ->tag('phpdoc.guides.markdown.parser.subParser')
        ->set(ListItemParser::class)
        ->arg('$subParsers', tagged_iterator('phpdoc.guides.markdown.parser.subParser'))
        ->tag('phpdoc.guides.markdown.parser.blockParser')
        ->set(ParagraphParser::class)
        ->arg('$inlineParsers', tagged_iterator('phpdoc.guides.markdown.parser.inlineParser'))
        ->tag('phpdoc.guides.markdown.parser.blockParser')
        ->tag('phpdoc.guides.markdown.parser.subParser')
        ->set(SeparatorParser::class)
        ->tag('phpdoc.guides.markdown.parser.blockParser')
        ->tag('phpdoc.guides.markdown.parser.subParser')
        ->set(CodeBlockParser::class)
        ->tag('phpdoc.guides.markdown.parser.blockParser')
        ->tag('phpdoc.guides.markdown.parser.subParser')

        ->set(TableParser::class)
        ->arg('$subParsers', tagged_iterator('phpdoc.guides.markdown.parser.inlineParser'))
        ->tag('phpdoc.guides.markdown.parser.blockParser')

        ->set(EmphasisParser::class)
        ->arg('$inlineParsers', tagged_iterator('phpdoc.guides.markdown.parser.inlineParser'))
        ->tag('phpdoc.guides.markdown.parser.inlineParser')
        ->set(LinkParser::class)
        ->arg('$inlineParsers', tagged_iterator('phpdoc.guides.markdown.parser.inlineParser'))
        ->tag('phpdoc.guides.markdown.parser.inlineParser')
        ->set(PlainTextParser::class)
        ->tag('phpdoc.guides.markdown.parser.inlineParser')
        ->set(StrongParser::class)
        ->arg('$inlineParsers', tagged_iterator('phpdoc.guides.markdown.parser.inlineParser'))
        ->tag('phpdoc.guides.markdown.parser.inlineParser')
        ->set(InlineCodeParser::class)
        ->tag('phpdoc.guides.markdown.parser.inlineParser')
        ->set(InlineImageParser::class)
        ->arg('$inlineParsers', tagged_iterator('phpdoc.guides.markdown.parser.inlineParser'))
        ->tag('phpdoc.guides.markdown.parser.inlineParser')
        ->set(NewLineParser::class)
        ->tag('phpdoc.guides.markdown.parser.inlineParser')

        ->set(FrontMatterParser::class)
        ->arg('$fieldParsers', tagged_iterator('phpdoc.guides.markdown.front_matter', 'fieldName'))
        ->tag('phpdoc.guides.markdown.parser.blockParser')

        ->set(TitleParser::class)
        ->tag('phpdoc.guides.markdown.front_matter')

        ->set(MarkupLanguageParser::class)
        ->arg('$parsers', tagged_iterator('phpdoc.guides.markdown.parser.blockParser'))
        ->tag('phpdoc.guides.parser.markupLanguageParser');
};
