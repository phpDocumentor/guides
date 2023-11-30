<?php

declare(strict_types=1);

use phpDocumentor\Guides\Graphs\Directives\UmlDirective;
use phpDocumentor\Guides\Graphs\Nodes\UmlNode;
use phpDocumentor\Guides\Graphs\Renderer\DiagramRenderer;
use phpDocumentor\Guides\Graphs\Renderer\PlantumlRenderer;
use phpDocumentor\Guides\Graphs\Renderer\PlantumlServerRenderer;
use phpDocumentor\Guides\Graphs\Renderer\TestRenderer;
use phpDocumentor\Guides\Graphs\Twig\UmlExtension;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->set(UmlDirective::class)
        ->tag('phpdoc.guides.directive')

        ->set('phpdoc.guides.', TemplateNodeRenderer::class)
        ->tag('phpdoc.guides.noderenderer.html')
        ->arg('$template', 'body/uml.html.twig')
        ->arg('$nodeClass', UmlNode::class)

        ->set(PlantumlRenderer::class)
        ->arg('$plantUmlBinaryPath', '%guides.graphs.plantuml_binary%')
        ->tag('phpdoc.guides.graph.renderer', ['alias' => 'plantuml'])

        ->set(TestRenderer::class)
        ->tag('phpdoc.guides.graph.renderer', ['alias' => 'testrender'])

        ->set(PlantumlServerRenderer::class)
        ->arg(
            '$plantumlServerUrl',
            '%guides.graphs.plantuml_server%',
        )
        ->tag('phpdoc.guides.graph.renderer', ['alias' => 'plantuml-server'])
        ->alias(DiagramRenderer::class, PlantumlServerRenderer::class)

        ->set(UmlExtension::class)
        ->arg('$renderers', tagged_iterator('phpdoc.guides.graph.renderer', 'alias'))
        ->arg('$rendererAlias', param('guides.graphs.renderer'))
        ->tag('twig.extension');
};
