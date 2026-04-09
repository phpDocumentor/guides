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

namespace phpDocumentor\Guides\Pages\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_filter;
use function str_contains;

/** @covers \phpDocumentor\Guides\Pages\DependencyInjection\PagesExtension */
final class PagesExtensionTest extends TestCase
{
    public function testPrependRegistersPageTemplatesUnderGuidesExtension(): void
    {
        $container = new ContainerBuilder();
        $extension = new PagesExtension();

        $extension->prepend($container);

        $configs = $container->getExtensionConfig('guides');
        self::assertNotEmpty($configs, 'Expected at least one prepended guides config block');

        // Collect all prepended templates across every config block
        $allTemplates = [];
        foreach ($configs as $block) {
            foreach ($block['templates'] ?? [] as $tpl) {
                $allTemplates[] = $tpl;
            }
        }

        $pageTemplates = array_filter(
            $allTemplates,
            static fn (array $t): bool => ($t['format'] ?? '') === 'page',
        );

        self::assertNotEmpty($pageTemplates, 'PagesExtension should register at least one "page" format template');
    }

    public function testPrependRegistersBaseTemplatePath(): void
    {
        $container = new ContainerBuilder();
        $extension = new PagesExtension();

        $extension->prepend($container);

        $configs = $container->getExtensionConfig('guides');
        $allPaths = [];
        foreach ($configs as $block) {
            foreach ($block['base_template_paths'] ?? [] as $path) {
                $allPaths[] = $path;
            }
        }

        self::assertNotEmpty($allPaths, 'PagesExtension should prepend at least one base_template_path');

        $hasPageTemplateDir = false;
        foreach ($allPaths as $path) {
            if (str_contains($path, 'guides-pages') && str_contains($path, 'template/page')) {
                $hasPageTemplateDir = true;
                break;
            }
        }

        self::assertTrue($hasPageTemplateDir, 'Expected a base_template_path pointing to the guides-pages template/page directory');
    }
}
