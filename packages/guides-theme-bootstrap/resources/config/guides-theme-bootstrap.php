<?php

declare(strict_types=1);

use phpDocumentor\Guides\Bootstrap\Directives\CardDirective;
use phpDocumentor\Guides\Bootstrap\Directives\CardFooterDirective;
use phpDocumentor\Guides\Bootstrap\Directives\CardGridDirective;
use phpDocumentor\Guides\Bootstrap\Directives\CardGroupDirective;
use phpDocumentor\Guides\Bootstrap\Directives\CardHeaderDirective;
use phpDocumentor\Guides\Bootstrap\Directives\CardImageDirective;
use phpDocumentor\Guides\Bootstrap\Directives\TabDirective;
use phpDocumentor\Guides\Bootstrap\Directives\TabsDirective;
use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DirectiveContentRule;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->instanceof(SubDirective::class)
        ->bind('$startingRule', service(DirectiveContentRule::class))
        ->instanceof(BaseDirective::class)
        ->tag('phpdoc.guides.directive')
        ->set(CardDirective::class)
        ->set(CardFooterDirective::class)
        ->set(CardHeaderDirective::class)
        ->set(CardImageDirective::class)
        ->set(CardGroupDirective::class)
        ->set(CardGridDirective::class)
        ->set(TabDirective::class)
        ->set(TabsDirective::class);
};
