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
        $this->defaultTextRoleFactory->registerTextRole(new AbbreviationTextRole($this->logger));
        $textRole = $this->defaultTextRoleFactory->getTextRole('abbreviation');
        self::assertInstanceOf(AbbreviationTextRole::class, $textRole);
    }
}
