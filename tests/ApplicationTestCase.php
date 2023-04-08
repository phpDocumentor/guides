<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Console\Application;
use phpDocumentor\Guides\Console\DependencyInjection\Compiler\NodeRendererPass;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function dirname;
use function getcwd;
use function rtrim;

class ApplicationTestCase extends TestCase
{
    private static ContainerBuilder $container;

    /** @beforeClass */
    public static function prepareContainer(): void
    {
        self::$container = new ContainerBuilder();

        // Load manual parameters
        self::$container->setParameter('vendor_dir', dirname(__DIR__) . '/vendor');
        self::$container->setParameter('working_directory', rtrim(getcwd(), '/'));

        // Load container configuration
        foreach (Application::getDefaultExtensions() as $extension) {
            self::$container->registerExtension($extension);
            self::$container->loadFromExtension($extension->getAlias());
        }

        self::$container->addCompilerPass(new NodeRendererPass());
        self::$container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                $container->getDefinition(Parser::class)->setPublic(true);
                $container->getDefinition(DelegatingNodeRenderer::class)->setPublic(true);
            }
        });

        // Compile container
        self::$container->compile(true);
    }

    public function getContainer(): ContainerBuilder
    {
        return self::$container;
    }
}
