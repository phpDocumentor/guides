<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Graphs\DependencyInjection;

use phpDocumentor\Guides\Graphs\Nodes\UmlNode;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;
use phpDocumentor\Guides\TemplateRenderer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use Symfony\Component\DependencyInjection\Reference;
use function dirname;

class GraphsExtension extends Extension implements PrependExtensionInterface
{
    private const HTML = [UmlNode::class => 'body/directive/uml.html.twig'];
    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        foreach (self::HTML as $node => $template) {
            $definition = new Definition(
                TemplateNodeRenderer::class,
                [
                    '$renderer' => new Reference(TemplateRenderer::class),
                    '$template' => $template,
                    '$nodeClass' => $node,
                ],
            );
            $definition->addTag('phpdoc.guides.noderenderer.html');

            $container->setDefinition('phpdoc.guides.rst.' . substr(strrchr($node, '\\') ?: '', 1), $definition);
        }
    }
    public function prepend(ContainerBuilder $container): void
    {

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 3) . '/resources/config'),
        );
        $templatePath = dirname(__DIR__, 3) . '/resources/template';
        $container->prependExtensionConfig('guides', [
            'base_template_paths' => [$templatePath],
        ]);
        $loader->load('guides-graphs.php');
    }
}
