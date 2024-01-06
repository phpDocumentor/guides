<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\Interlink;

use phpDocumentor\Guides\Interlink\Exception\InvalidInventoryLink;
use PHPUnit\Framework\TestCase;

final class InventoryLinkTest extends TestCase
{
    public function testHtmlLinkSet(): void
    {
        $link          = 'SomeThing.html';
        $inventoryLink = new InventoryLink('', '', $link, '');
        self::assertEquals($inventoryLink->getPath(), $link);
    }

    public function testHtmlLinkWithPathSet(): void
    {
        $link          = 'Some/Path/SomeThing.html';
        $inventoryLink = new InventoryLink('', '', $link, '');
        self::assertEquals($inventoryLink->getPath(), $link);
    }

    public function testHtmlLinkWithPathAndAnchorSet(): void
    {
        $link          = 'Some/Path/SomeThing.html#anchor';
        $inventoryLink = new InventoryLink('', '', $link, '');
        self::assertEquals($inventoryLink->getPath(), $link);
    }

    public function testHtmlLinkWithPathAndSpecialSignsInAnchor(): void
    {
        $link          = 'WritingReST/Reference/Code/Phpdomain.html#TYPO3\CMS\Core\Context\ContextAwareTrait::$context';
        $inventoryLink = new InventoryLink('', '', $link, '');
        self::assertEquals($inventoryLink->getPath(), $link);
    }

    public function testLinkMayContaintDot(): void
    {
        $link          = 'WritingReST/Reference/Code/3.14/Phpdomain.html';
        $inventoryLink = new InventoryLink('', '', $link, '');
        self::assertEquals($inventoryLink->getPath(), $link);
    }

    public function testPhpLinkThrowsError(): void
    {
        $link = 'Some/Path/SomeThing.php#anchor';
        $this->expectException(InvalidInventoryLink::class);
        new InventoryLink('', '', $link, '');
    }

    public function testJavaScriptLinkThrowsError(): void
    {
        $link = 'javascript:alert()';
        $this->expectException(InvalidInventoryLink::class);
        new InventoryLink('', '', $link, '');
    }

    public function testUrlLinkThrowsError(): void
    {
        $link = 'https://example.com';
        $this->expectException(InvalidInventoryLink::class);
        new InventoryLink('', '', $link, '');
    }
}
