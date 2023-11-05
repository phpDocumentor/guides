<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\DependencyInjection;

use phpDocumentor\Guides\RestructuredText\DependencyInjection\Compiler\TextRolePass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function dirname;

class ReStructuredTextExtension extends Extension implements PrependExtensionInterface, CompilerPassInterface
{
    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 3) . '/resources/config'),
        );

        $loader->load('guides-restructured-text.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('guides', [
            'base_template_paths' => [
                dirname(__DIR__, 3) . '/resources/template/html',
                dirname(__DIR__, 3) . '/resources/template/latex',
            ],
        ]);
    }

    public function process(ContainerBuilder $container): void
    {
        (new TextRolePass())->process($container);
    }
}
