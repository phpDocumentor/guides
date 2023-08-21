<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Twig\Theme;

use PHPUnit\Framework\TestCase;
use Twig\Loader\FilesystemLoader;

final class ThemeManagerTest extends TestCase
{
    public function testDefaultPaths(): void
    {
        $loader = new FilesystemLoader([], __DIR__ . '/fixtures');

        $manager = new ThemeManager($loader, ['default-one', 'default-two']);

        self::assertEquals(['default-one', 'default-two'], $loader->getPaths());
    }

    public function testSingleTheme(): void
    {
        $loader = new FilesystemLoader([], __DIR__ . '/fixtures');

        $manager = new ThemeManager($loader, ['default-one', 'default-two']);
        $manager->registerTheme(new ThemeConfig('custom', ['custom-theme']));

        $manager->useTheme('custom');

        self::assertEquals(['custom-theme', 'default-one', 'default-two'], $loader->getPaths());
    }

    public function testSingleThemeWithMultiplePaths(): void
    {
        $loader = new FilesystemLoader([], __DIR__ . '/fixtures');

        $manager = new ThemeManager($loader, ['default-one', 'default-two']);
        $manager->registerTheme(new ThemeConfig('custom', ['custom-theme', 'child-theme']));

        $manager->useTheme('custom');

        self::assertEquals(['custom-theme', 'child-theme', 'default-one', 'default-two'], $loader->getPaths());
    }

    public function testThemeInheritance(): void
    {
        $loader = new FilesystemLoader([], __DIR__ . '/fixtures');

        $manager = new ThemeManager($loader, ['default-one', 'default-two']);
        $manager->registerTheme(new ThemeConfig('custom', ['custom-theme']));
        $manager->registerTheme(new ThemeConfig('child', ['child-theme'], 'custom'));

        $manager->useTheme('child');

        self::assertEquals(['child-theme', 'custom-theme', 'default-one', 'default-two'], $loader->getPaths());
    }
}
