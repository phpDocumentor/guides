<?php

declare(strict_types=1);

use Highlight\Highlighter as HighlightPHP;
use phpDocumentor\Guides\Code\Highlighter\Highlighter;
use phpDocumentor\Guides\Code\Twig\CodeExtension as TwigExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()

        ->set(HighlightPHP::class)

        ->set(Highlighter::class)
        ->args([
            '$languageAliases' => [],
        ])

        ->set(TwigExtension::class)
        ->tag('twig.extension');
};
