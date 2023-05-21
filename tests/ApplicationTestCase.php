<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Cli\DependencyInjection\ApplicationExtension;
use phpDocumentor\Guides\Cli\DependencyInjection\ContainerFactory;
use phpDocumentor\Guides\DependencyInjection\TestExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

use function dirname;

class ApplicationTestCase extends TestCase
{
    private Container|null $container = null;

    public function getContainer(): Container
    {
        if ($this->container === null) {
            $this->prepareContainer();
        }

        return $this->container;
    }

    /**
     * @param array<string, array<mixed>> $configuration
     * @param list<ExtensionInterface> $extraExtensions
     *
     * @phpstan-assert Container $this->container
     */
    protected function prepareContainer(array $configuration = [], array $extraExtensions = []): void
    {
        $containerFactory = new ContainerFactory([
            new ApplicationExtension(),
            new TestExtension(),
            ...$extraExtensions,
        ]);

        foreach ($configuration as $extension => $extensionConfig) {
            $containerFactory->loadExtensionConfig($extension, $extensionConfig);
        }

        $this->container = $containerFactory->create(dirname(__DIR__) . '/vendor');
    }
}
