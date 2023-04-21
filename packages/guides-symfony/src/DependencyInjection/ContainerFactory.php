<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\DependencyInjection;

use LogicException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use phpDocumentor\Guides\DependencyInjection\Compiler\ParserRulesPass;
use phpDocumentor\Guides\DependencyInjection\GuidesExtension;
use phpDocumentor\Guides\RestructuredText\DependencyInjection\ReStructuredTextExtension;

final class ContainerFactory
{
    private ContainerBuilder $container;

    /** @var array<string, string> */
    private array $registeredExtensions = [];

    /**
     * @param list<ExtensionInterface> $defaultExtensions
     */
    public function __construct(array $defaultExtensions = [])
    {
        $this->container = new ContainerBuilder();

        $this->container->addCompilerPass(new ParserRulesPass());

        foreach (array_merge([new GuidesExtension(), new ReStructuredTextExtension()], $defaultExtensions) as $extension) {
            $this->registerExtension($extension);
        }
    }

    /**
     * @param array<mixed> $config
     */
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

    /**
     * @param array<mixed> $config
     */
    private function registerExtension(ExtensionInterface $extension, array $config = []): void
    {
        $this->container->registerExtension($extension);
        $this->container->loadFromExtension($extension->getAlias());

        if ($extension instanceof Extension) {
            $this->container->addCompilerPass($extension);
        }

        $this->registeredExtensions[get_class($extension)] = $extension->getAlias();
    }

    /**
     * @return class-string<ExtensionInterface>
     */
    private function resolveExtensionClass(string $name): string
    {
        $fqcn = $name;
        if (!class_exists($fqcn)) {
            [$namespace, $package] = explode('\\', $fqcn, 2);

            $fqcn = implode('\\', [$namespace, 'DependencyInjection', $package.'Extension']);
            if (!class_exists($fqcn)) {
                $fqcn = 'phpDocumentor\\'.$fqcn;

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
