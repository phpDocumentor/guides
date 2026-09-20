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

namespace phpDocumentor\Guides\Llms\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function dirname;

/**
 * Symfony DI extension for the guides-llms package.
 *
 * The package holds the output that describes a manual to the tools and
 * language models that read it rather than to a person: what a reader gets
 * from the rendered pages and the navigation around them, a program has to be
 * told. The "toc" output format is the first of it, writing the manual's table
 * of contents as "toc.json"; more is expected to follow, which is why this is
 * a package rather than a file.
 *
 * Everything it registers is rendered beside whatever else the project
 * renders, and enabling the extension is all there is to it:
 *
 * ```xml
 * <extension class="phpDocumentor\Guides\Llms\DependencyInjection\LlmsExtension"/>
 * ```
 */
final class LlmsExtension extends Extension
{
    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 3) . '/resources/config'),
        );

        $loader->load('guides-llms.php');
    }
}
