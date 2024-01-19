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

use phpDocumentor\Guides\Nodes\Links\InvalidLink;
use PHPUnit\Framework\TestCase;

final class InvalidLinkTest extends TestCase
{
    public function test_it_has_a_name(): void
    {
        $invalidLink = new InvalidLink('name');

        self::assertSame('name', $invalidLink->getName());
    }
}
