<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\DependencyInjection;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;

use function dirname;
use function sprintf;

class ApplicationExtension extends Extension implements CompilerPassInterface
{
    /** @param string[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->setAlias(ContainerInterface::class, 'service_container');

        $loader = new PhpFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 2) . '/resources/config'),
        );

        $loader->load('services.php');
    }

    public function getAlias(): string
    {
        return 'application';
    }

    public function process(ContainerBuilder $container): void
    {
        $eventDispatcher = $container->getDefinition(EventDispatcher::class);

        foreach ($container->findTaggedServiceIds('event_listener') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['event'])) {
                    throw new InvalidArgumentException(sprintf('Service "%s" must define the "event" attribute on "event_listener" tags.', $id));
                }

                $eventDispatcher->addMethodCall(
                    'addListener',
                    [
                        $tag['event'],
                        [new Reference($id), $tag['method'] ?? '__invoke'],
                    ],
                );
            }
        }
    }
}
