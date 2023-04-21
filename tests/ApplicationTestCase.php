<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Cli\Application;
use phpDocumentor\Guides\Cli\DependencyInjection\ApplicationExtension;
use phpDocumentor\Guides\DependencyInjection\Compiler\NodeRendererPass;
use phpDocumentor\Guides\DependencyInjection\Compiler\ParserRulesPass;
use phpDocumentor\Guides\DependencyInjection\ContainerFactory;
use phpDocumentor\Guides\DependencyInjection\TestExtension;
use phpDocumentor\Guides\NodeRenderers\DelegatingNodeRenderer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function dirname;
use function getcwd;
use function rtrim;

class ApplicationTestCase extends TestCase
{
    private ?Container $container = null;

    public function getContainer(): ContainerBuilder
    {
        if (null === $this->container) {
            $this->prepareContainer();
        }

        return $this->container;
    }

    /** @param array<string, array<mixed>> $configuration */
    protected function prepareContainer(array $configuration = []): void
    {
        $containerFactory = new ContainerFactory([
            new ApplicationExtension(),
            new TestExtension(),
        ]);

        foreach ($configuration as $extension => $extensionConfig) {
            $containerFactory->loadExtensionConfig($extension, $extensionConfig);
        }

        $this->container = $containerFactory->create(dirname(__DIR__) . '/vendor');
    }
}
