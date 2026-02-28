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
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Exception\RuntimeException as DIRuntimeException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

use function array_keys;
use function array_merge;
use function assert;
use function class_exists;
use function file_exists;
use function file_put_contents;
use function function_exists;
use function getcwd;
use function implode;
use function is_a;
use function is_dir;
use function md5;
use function mkdir;
use function opcache_invalidate;
use function rtrim;
use function serialize;
use function sprintf;
use function strrchr;
use function substr;

final class ContainerFactory
{
    private const CACHE_DIR = '/tmp/guides-container-cache';
    private const CACHE_CLASS = 'CachedGuidesContainer';

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
            $this->registerExtension($extension, []);
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

    public function addConfigFile(string $filePath): void
    {
        $this->configs = array_merge($this->configs, $this->configLoader->load($filePath));
    }

    public function create(string $vendorDir): Container
    {
        $cacheKey = $this->generateCacheKey($vendorDir);
        $cacheFile = self::CACHE_DIR . '/' . self::CACHE_CLASS . '_' . $cacheKey . '.php';
        $cacheClass = self::CACHE_CLASS . '_' . $cacheKey;

        // Try to load cached container
        if (file_exists($cacheFile)) {
            require_once $cacheFile;
            if (class_exists($cacheClass, false)) {
                $container = new $cacheClass();
                assert($container instanceof Container);

                return $container;
            }
        }

        // Build container
        $this->processConfig();
        $this->container->setParameter('vendor_dir', $vendorDir);
        $this->container->setParameter('working_directory', rtrim(getcwd(), '/'));
        $this->container->compile(true);

        // Try to cache the compiled container (may fail if container has object parameters)
        try {
            $this->cacheContainer($cacheFile, $cacheClass);
        } catch (DIRuntimeException) {
            // Container cannot be cached (has object/resource parameters), continue without caching
        }

        return $this->container;
    }

    private function generateCacheKey(string $vendorDir): string
    {
        $workingDir = getcwd();
        $configData = [
            'vendor_dir' => $vendorDir,
            'working_dir' => $workingDir !== false ? rtrim($workingDir, '/') : '',
            'extensions' => array_keys($this->registeredExtensions),
            'configs' => serialize($this->configs),
        ];

        return substr(md5(serialize($configData)), 0, 12);
    }

    private function cacheContainer(string $cacheFile, string $cacheClass): void
    {
        if (!is_dir(self::CACHE_DIR)) {
            @mkdir(self::CACHE_DIR, 0755, true);
        }

        $dumper = new PhpDumper($this->container);
        $code = $dumper->dump([
            'class' => $cacheClass,
            'base_class' => Container::class,
        ]);

        file_put_contents($cacheFile, $code);

        // Invalidate opcache for the new file
        if (!function_exists('opcache_invalidate')) {
            return;
        }

        opcache_invalidate($cacheFile, true);
    }

    /** @param array<mixed> $config */
    private function registerExtension(ExtensionInterface $extension, array $config): void
    {
        $this->container->registerExtension($extension);
        $this->container->loadFromExtension($extension->getAlias(), $config);

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

        $guidesConfig = [];
        foreach ($config as $key => $value) {
            if ($key === 'extensions') {
                continue;
            }

            $guidesConfig[$key] = $value;
            unset($config[$key]);
        }

        $config['extensions'][] = ['class' => GuidesExtension::class] + $guidesConfig;

        foreach ($config['extensions'] as $extension) {
            $extensionFqcn = $this->resolveExtensionClass($extension['class']);
            unset($extension['class']);

            $this->loadExtensionConfig($extensionFqcn, $extension);
        }
    }
}
