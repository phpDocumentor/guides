<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Meta\DocumentEntry;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function md5;

final class RenderContextTest extends TestCase
{
    #[DataProvider('documentPathProvider')]
    public function testRelativeDocUrl(
        string $filePath,
        string $destinationPath,
        string $linkedDocument,
        string $result,
        ?string $anchor = null
    ): void {
        $documentNode = new DocumentNode(md5('hash'), $filePath);

        $context = RenderContext::forDocument(
            $documentNode,
            $this->createStub(FilesystemInterface::class),
            $this->createStub(FilesystemInterface::class),
            $destinationPath,
            new Metas([
                'getting-started/configuration' => new DocumentEntry(
                    'getting-started/configuration',
                    TitleNode::emptyNode()
                ),
            ]),
            new UrlGenerator(),
            'txt'
        );

        self::assertSame($result, $context->relativeDocUrl($linkedDocument, $anchor));
    }

    /** @return string[][] */
    public static function documentPathProvider(): array
    {
        return [
            [
                'filePath' => 'getting-started/configuration',
                'destinationPath' => 'guide/',
                'linkedDocument' => 'installing',
                'result' => 'guide/getting-started/installing.txt',
            ],
            [
                'filePath' => 'getting-started/configuration',
                'destinationPath' => 'guide/',
                'linkedDocument' => '/installing',
                'result' => 'guide/installing.txt',
            ],
            [
                'filePath' => 'getting-started/configuration',
                'destinationPath' => 'guide',
                'linkedDocument' => 'getting-started/configuration',
                'result' => 'guide/getting-started/configuration.txt#composer',
                'anchor' => 'composer',
            ],
            [
                'filePath' => 'getting-started/configuration',
                'destinationPath' => 'guide/',
                'linkedDocument' => '../references/installing',
                'result' => 'guide/references/installing.txt',
            ],
        ];
    }
}
