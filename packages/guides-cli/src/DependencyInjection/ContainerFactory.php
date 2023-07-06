<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\DependencyInjection;

use LogicException;
use phpDocumentor\Guides\Cli\Config\Configuration;
use phpDocumentor\Guides\Cli\Config\XmlFileLoader;
use phpDocumentor\Guides\DependencyInjection\GuidesExtension;
use phpDocumentor\Guides\RestructuredText\DependencyInjection\ReStructuredTextExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

use function array_merge;
use function class_exists;
use function getcwd;
use function implode;
use function is_a;
use function rtrim;
use function sprintf;
use function strrchr;
use function substr;

final class ContainerFactory
{
    private readonly ContainerBuilder $container;
    private readonly XmlFileLoader $configLoader;

    /** @var array<string, string> */
    private array $registeredExtensions = [];

    /** @var list<array<mixed>> */
    private array $configs = [];

    /** @param list<ExtensionInterface> $defaultExtensions */
    public function __construct(array $defaultExtensions = [])
    {
        $this->container = new ContainerBuilder();
        $this->configLoader = new XmlFileLoader(new FileLocator());

        foreach ([new GuidesExtension(), new ReStructuredTextExtension(), ...$defaultExtensions] as $extension) {
            $this->registerExtension($extension);
        }
    }

    /** @param array<mixed> $config */
    public function loadExtensionConfig(string $extension, array $config): void
    {
        $extensionFqcn = $this->resolveExtensionClass($extension);

        $extensionAlias = $this->registeredExtensions[$extensionFqcn] ?? false;
        if (!$extensionAlias) {
            $this->registerExtension(new $extensionFqcn());

            return;
        }

        $this->container->loadFromExtension($extensionAlias, $config);
    }

    public function addConfigFile(string $filePath): void
    {
        $this->configs = array_merge($this->configs, $this->configLoader->load($filePath));
    }

    public function create(string $vendorDir): Container
    {
        $this->processConfig();

        $this->container->setParameter('vendor_dir', $vendorDir);
        $this->container->setParameter('working_directory', rtrim(getcwd(), '/'));

        $this->container->compile(true);

        return $this->container;
    }

    private function registerExtension(ExtensionInterface $extension): void
    {
        $this->container->registerExtension($extension);
        $this->container->loadFromExtension($extension->getAlias());

        $this->registeredExtensions[$extension::class] = $extension->getAlias();
    }

    /** @return class-string<ExtensionInterface> */
    private function resolveExtensionClass(string $name): string
    {
        $fqcn = $name;
        if (!class_exists($fqcn)) {
            $package = substr(strrchr($fqcn, '\\') ?: '', 1);

            $fqcn = implode('\\', [$fqcn, 'DependencyInjection', $package . 'Extension']);
            if (!class_exists($fqcn)) {
                throw new LogicException(sprintf('Extension "%s" does not exist.', $fqcn));
            }
        }

        if (!is_a($fqcn, ExtensionInterface::class, true)) {
            throw new LogicException(sprintf('Extension "%s" does not exist.', $fqcn));
        }

        return $fqcn;
    }

    private function processConfig(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $this->configs);

        foreach ($config['extensions'] as $extension) {
            $extensionFqcn = $this->resolveExtensionClass($extension['class']);

            $this->registerExtension(new $extensionFqcn());
        }
    }
}
