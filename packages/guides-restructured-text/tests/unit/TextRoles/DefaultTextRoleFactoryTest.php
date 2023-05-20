<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class DefaultTextRoleFactoryTest extends TestCase
{
    private Logger $logger;
    private DefaultTextRoleFactory $defaultTextRoleFactory;

    public function setUp(): void
    {
        $this->logger = new Logger('test');
        $this->defaultTextRoleFactory = new DefaultTextRoleFactory(
            $this->logger,
            new GenericTextRole(),
            [],
            [],
        );
    }

    public function testUnknownTextRoleReturnsGenericTextRole(): void
    {
        $textRole = $this->defaultTextRoleFactory->getTextRole('unknown');
        self::assertInstanceOf(GenericTextRole::class, $textRole);
    }

    public function testUnknownDomainReturnsGenericTextRole(): void
    {
        $textRole = $this->defaultTextRoleFactory->getTextRole('unknown', 'unknown');
        self::assertInstanceOf(GenericTextRole::class, $textRole);
    }

    public function testRegisteredTextRoleIsReturned(): void
    {
        $this->defaultTextRoleFactory->registerTextRole(new EmphasisTextRole());
        $textRole = $this->defaultTextRoleFactory->getTextRole('emphasis');
        self::assertInstanceOf(EmphasisTextRole::class, $textRole);
    }
}
