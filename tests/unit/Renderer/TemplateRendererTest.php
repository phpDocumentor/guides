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

namespace phpDocumentor\Guides\Renderer;

use phpDocumentor\Guides\Twig\EnvironmentBuilder;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

use function sprintf;

/**
 * @coversDefaultClass \phpDocumentor\Guides\Renderer\TemplateRenderer
 * @covers ::<private>
 */
final class TemplateRendererTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::render
     */
    public function testRenderTemplateUsingProvidedTwigEnvironment(): void
    {
        $renderedOutput = 'some text';
        $basePath = '/base/path';
        $template = 'mytemplate.html.twig';
        $data = ['key1' => 'value2'];

        $twig = $this->prophesize(Environment::class);
        $twig->render(sprintf('%s/%s', $basePath, $template), $data)->willReturn($renderedOutput);

        $enviromentBuilder = new EnvironmentBuilder();
        $enviromentBuilder->setEnvironmentFactory(static function () use ($twig) {
            return $twig->reveal();
        });

        $renderer = new TemplateRenderer($enviromentBuilder, $basePath);

        self::assertSame($renderedOutput, $renderer->render($template, $data));
    }
}
