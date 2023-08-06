<?php

declare(strict_types=1);

namespace T3Docs\Typo3DocsTheme\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use function dirname;

class Typo3DocsThemeExtension extends Extension implements PrependExtensionInterface
{
    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    public function prepend(ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 2) . '/resources/config'),
        );
        $loader->load('your-extension.php');
        $container->prependExtensionConfig('guides', [
            'themes' => [
                'typo3docs' => [
                    'extends' => 'bootstrap',
                    'templates' => [dirname(__DIR__, 2) . '/resources/template'],
                ],
            ],
        ]);
    }
}
