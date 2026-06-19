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

namespace phpDocumentor\Guides\Pages\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/** @covers \phpDocumentor\Guides\Pages\DependencyInjection\Configuration */
final class ConfigurationTest extends TestCase
{
    private Processor $processor;
    private Configuration $configuration;

    protected function setUp(): void
    {
        $this->processor     = new Processor();
        $this->configuration = new Configuration();
    }

    public function testDefaultSourceDirectoryIsPagesWhenNotConfigured(): void
    {
        $config = $this->processor->processConfiguration($this->configuration, []);

        self::assertSame('pages', $config['source_directory']);
    }

    public function testSourceDirectoryCanBeOverridden(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [['source_directory' => 'static-pages']],
        );

        self::assertSame('static-pages', $config['source_directory']);
    }
}
