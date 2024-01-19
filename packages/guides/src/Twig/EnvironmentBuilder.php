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

namespace phpDocumentor\Guides\Twig;

use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extension\ExtensionInterface;

final class EnvironmentBuilder
{
    private Environment $environment;

    /** @param ExtensionInterface[] $extensions */
    public function __construct(ThemeManager $themeManager, iterable $extensions = [])
    {
        $this->environment = new Environment(
            $themeManager->getFilesystemLoader(),
            ['debug' => true],
        );
        $this->environment->addExtension(new DebugExtension());

        foreach ($extensions as $extension) {
            $this->environment->addExtension($extension);
        }
    }

    /** @param callable(): Environment $factory */
    public function setEnvironmentFactory(callable $factory): void
    {
        $this->environment = $factory();
    }

    public function setContext(RenderContext $context): void
    {
        $this->environment->addGlobal('env', $context);
    }

    public function getTwigEnvironment(): Environment
    {
        return $this->environment;
    }
}
