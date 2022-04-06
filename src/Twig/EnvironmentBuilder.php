<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Twig;

use phpDocumentor\Guides\RenderContext;
use Twig\Environment;
use Webmozart\Assert\Assert;

class EnvironmentBuilder
{
    private ?Environment $environment = null;

    public function setEnvironmentFactory(callable $factory): void
    {
        $this->environment = $factory();
    }

    public function setContext(RenderContext $context): void
    {
        Assert::isInstanceOf($this->environment, Environment::class);

        $this->environment->addGlobal('env', $context);
    }

    public function getTwigEnvironment(): Environment
    {
        Assert::isInstanceOf($this->environment, Environment::class);

        return $this->environment;
    }
}
