<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Twig;

use phpDocumentor\Guides\RenderContext;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;

class EnvironmentBuilder
{
    private Environment $environment;

    /** @param ExtensionInterface[] $extensions */
    public function __construct(iterable $extensions = [])
    {
        $this->environment = new Environment(
            new FilesystemLoader(
                [
                    __DIR__ . '/../../resources/template/html/guides',
                ]
            )
        );

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
