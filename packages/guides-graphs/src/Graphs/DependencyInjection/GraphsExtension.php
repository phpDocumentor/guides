<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\Guides\Graphs\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function dirname;

final class GraphsExtension extends Extension implements PrependExtensionInterface
{
    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $configs,
        );

        $container->setParameter('guides.graphs.renderer', $config['renderer']);
        $container->setParameter('guides.graphs.plantuml_binary', $config['plantuml_binary']);
        $container->setParameter('guides.graphs.plantuml_server', $config['plantuml_server']);

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 3) . '/resources/config'),
        );

        $loader->load('guides-graphs.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('guides', [
            'base_template_paths' => [dirname(__DIR__, 3) . '/resources/template/html'],
        ]);
    }
}
