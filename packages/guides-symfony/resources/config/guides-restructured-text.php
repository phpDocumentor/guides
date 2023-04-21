<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->instanceof(phpDocumentor\Guides\RestructuredText\Directives\Directive::class)
        ->tag('phpdoc.guides.directive')
        ->instanceof(phpDocumentor\Guides\NodeRenderers\NodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->load(
            'phpDocumentor\\Guides\\RestructuredText\\Directives\\',
            '%vendor_dir%/phpdocumentor/guides-restructured-text/src/RestructuredText/Directives',
        )
        ->load(
            'phpDocumentor\\Guides\RestructuredText\\NodeRenderers\\Html\\',
            '%vendor_dir%/phpdocumentor/guides-restructured-text/src/RestructuredText/NodeRenderers/Html',
        )
        ->set(phpDocumentor\Guides\RestructuredText\MarkupLanguageParser::class)
        ->args([
            '$startingRule' => service(
                phpDocumentor\Guides\RestructuredText\Parser\Productions\DocumentRule::class,
            ),
            '$directives' => tagged_iterator('phpdoc.guides.directive'),
        ])
        ->tag('phpdoc.guides.parser.markupLanguageParser')
        ->set(phpDocumentor\Guides\RestructuredText\Parser\Productions\DocumentRule::class)
        ->arg('$directiveHandlers', tagged_iterator('phpdoc.guides.directive'))
        ->set(phpDocumentor\Guides\RestructuredText\Span\SpanParser::class)
        ->set(phpDocumentor\Guides\RestructuredText\Toc\GlobSearcher::class)
        ->set(phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder::class);
};
