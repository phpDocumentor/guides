<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText\Parser;

use PHPUnit\Framework\TestCase;

class LinesIteratorTest extends TestCase
{
    /**
     * @dataProvider validDocumentProvider
     */
    public function testLoadLoadsValidDocument(string $document): void
    {
        $iterator = new LinesIterator();
        $iterator->load($document);

        $this->assertEquals(explode("\n", trim($document, "\n")), $iterator->toArray());
    }

    public function validDocumentProvider(): array
    {
        return [
            ["Hello, world!\n"],
            ["Hello,\nworld!\n"],
            ["Hello,\n\nworld!\n"],
            ["Hello,\n\tworld!\n"],
        ];
    }

    public function testLoadRemovesUTF8BOM(): void
    {
        $document = "\xef\xbb\xbfHello, world!\n";
        $iterator = new LinesIterator();
        $iterator->load($document);

        $this->assertEquals('Hello, world!', $iterator->current());
    }

    public function testIsEmptyLineReturnsFalseForValidLine(): void
    {
        $this->assertFalse(LinesIterator::isEmptyLine('Hello, world!'));
    }

    /**
     * @dataProvider emptyLineProvider
     */
    public function testIsEmptyLineReturnsTrueForEmptyLine(?string $line): void
    {
        $this->assertTrue(LinesIterator::isEmptyLine($line));
    }

    public function emptyLineProvider(): array
    {
        return [
            [''],
            ['  '],
            ["\t\n"],
        ];
    }

    /**
     * @dataProvider blockLinesProvider
     */
    public function testIsBlockLine(string $input): void
    {
        $iterator = new LinesIterator();
        $iterator->load($input);
        $iterator->next();

        $this->assertTrue($iterator->isBlockLine($iterator->current()));
    }

    public function blockLinesProvider(): array
    {
        return [
            [
            <<<RST
Test
   BlockLine
RST
            ],
            [
                <<<RST
Test




   BlockLine
RST
            ],
        ];
    }

    public function testNotABlockLine(): void
    {
        $iterator = new LinesIterator();
        $iterator->load(<<<RST
Test






OtherLine
RST
);
        $iterator->next();

        $this->assertFalse($iterator->isBlockLine($iterator->current()));
    }
}
