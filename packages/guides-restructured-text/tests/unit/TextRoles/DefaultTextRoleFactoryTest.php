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

namespace phpDocumentor\Guides\RestructuredText\TextRoles;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;

final class DefaultTextRoleFactoryTest extends TestCase
{
    private Logger $logger;
    private DefaultTextRoleFactory $defaultTextRoleFactory;

    public function setUp(): void
    {
        $this->logger = new Logger('test');
        $this->defaultTextRoleFactory = new DefaultTextRoleFactory(
            new GenericTextRole(),
            new LiteralTextRole(),
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
