<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Cli\DependencyInjection;

use LogicException;
use phpDocumentor\Guides\DependencyInjection\GuidesExtension;
use phpDocumentor\Guides\RestructuredText\DependencyInjection\ReStructuredTextExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

use function array_merge;
use function class_exists;
use function explode;
use function getcwd;
use function implode;
use function is_a;
use function rtrim;

final class ContainerFactory
{
    private ContainerBuilder $container;

    /** @var array<string, string> */
    private array $registeredExtensions = [];

    /** @param list<ExtensionInterface> $defaultExtensions */
    public function __construct(array $defaultExtensions = [])
    {
        $this->container = new ContainerBuilder();

        foreach (array_merge([new GuidesExtension(), new ReStructuredTextExtension()], $defaultExtensions) as $extension) {
            $this->registerExtension($extension);
        }
    }

    /** @param array<mixed> $config */
    public function loadExtensionConfig(string $extension, array $config): void
    {
        $extensionFqcn = $this->resolveExtensionClass($extension);

        $extensionAlias = $this->registeredExtensions[$extensionFqcn] ?? false;
        if (!$extensionAlias) {
            $this->registerExtension(new $extensionFqcn(), $config);

            return;
        }

        $this->container->loadFromExtension($extensionAlias, $config);
    }

    public function create(string $vendorDir): Container
    {
        $this->container->setParameter('vendor_dir', $vendorDir);
        $this->container->setParameter('working_directory', $workingDirectory = rtrim(getcwd(), '/'));

        $this->container->compile(true);

        return $this->container;
    }

    /** @param array<mixed> $config */
    private function registerExtension(ExtensionInterface $extension, array $config = []): void
    {
        $this->container->registerExtension($extension);
        $this->container->loadFromExtension($extension->getAlias());

        if ($extension instanceof CompilerPassInterface) {
            $this->container->addCompilerPass($extension);
        }

        $this->registeredExtensions[$extension::class] = $extension->getAlias();
    }

    /** @return class-string<ExtensionInterface> */
    private function resolveExtensionClass(string $name): string
    {
        $fqcn = $name;
        if (!class_exists($fqcn)) {
            [$namespace, $package] = explode('\\', $fqcn, 2);

            $fqcn = implode('\\', [$namespace, 'DependencyInjection', $package . 'Extension']);
            if (!class_exists($fqcn)) {
                $fqcn = 'phpDocumentor\\' . $fqcn;

                if (!class_exists($fqcn)) {
                    throw new LogicException();
                }
            }
        }

        if (!is_a($fqcn, ExtensionInterface::class, true)) {
            throw new LogicException();
        }

        return $fqcn;
    }
}
