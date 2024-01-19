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

namespace phpDocumentor\Guides\Bootstrap\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function dirname;

final class BootstrapExtension extends Extension implements PrependExtensionInterface
{
    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 3) . '/resources/config'),
        );
        $loader->load('guides-theme-bootstrap.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('guides', [
            'themes' => ['bootstrap' => dirname(__DIR__, 3) . '/resources/template'],
        ]);
    }
}
