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

namespace phpDocumentor\Guides;

use phpDocumentor\Guides\Cli\DependencyInjection\ApplicationExtension;
use phpDocumentor\Guides\Cli\DependencyInjection\ContainerFactory;
use phpDocumentor\Guides\DependencyInjection\TestExtension;
use phpDocumentor\Guides\Markdown\DependencyInjection\MarkdownExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

use function dirname;
use function getenv;

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
    protected function prepareContainer(string|null $configurationFile = null, array $configuration = [], array $extraExtensions = []): void
    {
        $containerFactory = new ContainerFactory([
            new ApplicationExtension(),
            new TestExtension(),
            new MarkdownExtension(),
            ...$extraExtensions,
        ]);

        foreach ($configuration as $extension => $extensionConfig) {
            $containerFactory->loadExtensionConfig($extension, $extensionConfig);
        }

        if ($configurationFile !== null) {
            $containerFactory->addConfigFile($configurationFile);
        }

        $containerFactory->setParameter('phpdoc.guides.parser.rst.disable_character_split', getenv('PHPDOC_GUIDES_PARSER_RST_DISABLE_CHARACTER_SPLIT') === 'true');

        $this->container = $containerFactory->create(dirname(__DIR__) . '/vendor');
    }
}
