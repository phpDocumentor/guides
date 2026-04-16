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

namespace phpDocumentor\Guides\Compiler\Passes;

use phpDocumentor\Guides\Build\IncrementalBuild\ContentHasher;
use phpDocumentor\Guides\Build\IncrementalBuild\IncrementalBuildState;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Nodes\CitationNode;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\InlineCompoundNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\Nodes\SectionNode;
use phpDocumentor\Guides\Nodes\TitleNode;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function file_put_contents;
use function is_link;
use function mkdir;
use function rmdir;
use function symlink;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

final class ExportsCollectorPassTest extends TestCase
{
    private IncrementalBuildState $buildState;
    private ContentHasher $hasher;
    private ExportsCollectorPass $pass;

    protected function setUp(): void
    {
        $this->buildState = new IncrementalBuildState();
        $this->hasher = new ContentHasher();
        $this->pass = new ExportsCollectorPass($this->buildState, $this->hasher);
    }

    public function testGetPriorityIsLow(): void
    {
        // Priority should be low to run after all other passes
        self::assertSame(10, $this->pass->getPriority());
    }

    public function testCollectsSectionTitles(): void
    {
        $document = new DocumentNode('hash1', 'docs/getting-started');
        $document->addChildNode(
            new SectionNode(
                new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Getting Started'), 1, 'getting-started'),
            ),
        );
        $document->addChildNode(
            new SectionNode(
                new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Installation'), 2, 'installation'),
            ),
        );

        $context = new CompilerContext(new ProjectNode());
        $this->pass->run([$document], $context);

        $exports = $this->buildState->getExports('docs/getting-started');
        self::assertNotNull($exports);
        self::assertArrayHasKey('getting-started', $exports->sectionTitles);
        self::assertArrayHasKey('installation', $exports->sectionTitles);
        self::assertSame('Getting Started', $exports->sectionTitles['getting-started']);
        self::assertSame('Installation', $exports->sectionTitles['installation']);
    }

    public function testCollectsCitations(): void
    {
        $document = new DocumentNode('hash1', 'docs/references');
        // CitationNode(array $value, string $name)
        $document->addChildNode(new CitationNode([], 'RFC7231'));
        $document->addChildNode(new CitationNode([], 'ECMA-262'));

        $context = new CompilerContext(new ProjectNode());
        $this->pass->run([$document], $context);

        $exports = $this->buildState->getExports('docs/references');
        self::assertNotNull($exports);
        self::assertContains('RFC7231', $exports->citations);
        self::assertContains('ECMA-262', $exports->citations);
    }

    public function testCollectsDuplicateCitations(): void
    {
        // Documents can have multiple citations with the same name (e.g., multiple
        // references to the same source). The collector captures all occurrences.
        $document = new DocumentNode('hash1', 'docs/with-duplicates');
        $document->addChildNode(new CitationNode([], 'RFC7231'));
        $document->addChildNode(new CitationNode([], 'RFC7231')); // Duplicate
        $document->addChildNode(new CitationNode([], 'ECMA-262'));

        $context = new CompilerContext(new ProjectNode());
        $this->pass->run([$document], $context);

        $exports = $this->buildState->getExports('docs/with-duplicates');
        self::assertNotNull($exports);
        // Duplicates are preserved (not deduplicated)
        self::assertCount(3, $exports->citations);
        self::assertSame(['RFC7231', 'RFC7231', 'ECMA-262'], $exports->citations);
    }

    public function testCollectsDocumentTitle(): void
    {
        $document = new DocumentNode('hash1', 'docs/index');
        $document->addChildNode(
            new SectionNode(
                new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Main Documentation'), 1, 'main-documentation'),
            ),
        );

        $context = new CompilerContext(new ProjectNode());
        $this->pass->run([$document], $context);

        $exports = $this->buildState->getExports('docs/index');
        self::assertNotNull($exports);
        self::assertSame('Main Documentation', $exports->documentTitle);
    }

    public function testComputesExportsHash(): void
    {
        $doc1 = new DocumentNode('hash1', 'doc1');
        $doc1->addChildNode(
            new SectionNode(
                new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title A'), 1, 'title-a'),
            ),
        );

        $doc2 = new DocumentNode('hash2', 'doc2');
        $doc2->addChildNode(
            new SectionNode(
                new TitleNode(InlineCompoundNode::getPlainTextInlineNode('Title B'), 1, 'title-b'),
            ),
        );

        $context = new CompilerContext(new ProjectNode());
        $this->pass->run([$doc1, $doc2], $context);

        $exports1 = $this->buildState->getExports('doc1');
        $exports2 = $this->buildState->getExports('doc2');

        self::assertNotNull($exports1);
        self::assertNotNull($exports2);
        // Different exports should have different hashes
        self::assertNotSame($exports1->exportsHash, $exports2->exportsHash);
    }

    public function testHashesSourceFileWhenInputDirSet(): void
    {
        $tempDir = sys_get_temp_dir() . '/exports-test-' . uniqid();
        mkdir($tempDir, 0755, true);
        $sourceFile = $tempDir . '/index.rst';
        file_put_contents($sourceFile, 'Test content for hashing');

        try {
            $this->buildState->setInputDir($tempDir);

            $document = new DocumentNode('hash1', 'index');
            $context = new CompilerContext(new ProjectNode());
            $this->pass->run([$document], $context);

            $exports = $this->buildState->getExports('index');
            self::assertNotNull($exports);
            self::assertNotEmpty($exports->contentHash);
            self::assertGreaterThan(0, $exports->lastModified);
        } finally {
            unlink($sourceFile);
            rmdir($tempDir);
        }
    }

    public function testReturnsDocumentsUnchanged(): void
    {
        $document = new DocumentNode('hash1', 'docs/test');

        $context = new CompilerContext(new ProjectNode());
        $result = $this->pass->run([$document], $context);

        self::assertSame([$document], $result);
    }

    public function testProcessesMultipleDocuments(): void
    {
        $doc1 = new DocumentNode('h1', 'doc1');
        $doc2 = new DocumentNode('h2', 'doc2');
        $doc3 = new DocumentNode('h3', 'doc3');

        $context = new CompilerContext(new ProjectNode());
        $this->pass->run([$doc1, $doc2, $doc3], $context);

        self::assertNotNull($this->buildState->getExports('doc1'));
        self::assertNotNull($this->buildState->getExports('doc2'));
        self::assertNotNull($this->buildState->getExports('doc3'));
    }

    public function testHandlesEmptyDocumentList(): void
    {
        $context = new CompilerContext(new ProjectNode());
        $result = $this->pass->run([], $context);

        self::assertSame([], $result);
        self::assertSame([], $this->buildState->getAllExports());
    }

    public function testFallsBackToZeroLastModifiedWhenNoSourceFile(): void
    {
        // When no source file exists (no inputDir set), the pass falls back to
        // hashing the document structure and uses current time. However, if
        // filemtime() were to fail on an existing file, it would use 0.
        // This test verifies the fallback behavior produces valid exports.
        $document = new DocumentNode('hash1', 'docs/no-source-file');

        // Don't set inputDir - simulates fallback path
        $context = new CompilerContext(new ProjectNode());
        $this->pass->run([$document], $context);

        $exports = $this->buildState->getExports('docs/no-source-file');
        self::assertNotNull($exports);
        // lastModified will be current time (>0) when fallback to document hash
        self::assertGreaterThan(0, $exports->lastModified);
        self::assertNotEmpty($exports->contentHash);
    }

    public function testPreventsPathTraversalAttack(): void
    {
        $tempDir = sys_get_temp_dir() . '/exports-traversal-test-' . uniqid();
        mkdir($tempDir . '/docs', 0755, true);

        // Create a file inside the input directory
        $insideFile = $tempDir . '/docs/legit.rst';
        file_put_contents($insideFile, 'Legitimate content');

        // Create a file OUTSIDE the input directory (sibling)
        $outsideFile = $tempDir . '/secret.txt';
        file_put_contents($outsideFile, 'SECRET DATA - should not be accessible');

        try {
            // Set input dir to the 'docs' subdirectory
            $this->buildState->setInputDir($tempDir . '/docs');

            // Try to access file via path traversal
            $document = new DocumentNode('hash1', '../secret');
            $context = new CompilerContext(new ProjectNode());
            $this->pass->run([$document], $context);

            $exports = $this->buildState->getExports('../secret');
            self::assertNotNull($exports);

            // The hash should be computed from document serialization (fallback),
            // NOT from the actual secret file content
            $secretFileHash = $this->hasher->hashFile($outsideFile);
            self::assertNotSame($secretFileHash, $exports->contentHash);
        } finally {
            if (file_exists($insideFile)) {
                unlink($insideFile);
            }

            if (file_exists($outsideFile)) {
                unlink($outsideFile);
            }

            rmdir($tempDir . '/docs');
            rmdir($tempDir);
        }
    }

    public function testPreventsPathTraversalWithSimilarDirectoryNames(): void
    {
        $tempDir = sys_get_temp_dir() . '/exports-prefix-test-' . uniqid();
        mkdir($tempDir . '/docs', 0755, true);
        mkdir($tempDir . '/docs-internal', 0755, true);

        // Create file in the sibling directory with similar prefix
        $siblingFile = $tempDir . '/docs-internal/secret.rst';
        file_put_contents($siblingFile, 'Internal secret content');

        try {
            // Set input dir to 'docs'
            $this->buildState->setInputDir($tempDir . '/docs');

            // Try to access file in docs-internal via traversal
            $document = new DocumentNode('hash1', '../docs-internal/secret');
            $context = new CompilerContext(new ProjectNode());
            $this->pass->run([$document], $context);

            $exports = $this->buildState->getExports('../docs-internal/secret');
            self::assertNotNull($exports);

            // Should NOT have accessed the sibling directory file
            $siblingHash = $this->hasher->hashFile($siblingFile);
            self::assertNotSame($siblingHash, $exports->contentHash);
        } finally {
            if (file_exists($siblingFile)) {
                unlink($siblingFile);
            }

            rmdir($tempDir . '/docs-internal');
            rmdir($tempDir . '/docs');
            rmdir($tempDir);
        }
    }

    public function testPreventsSymlinkPathTraversalAttack(): void
    {
        $tempDir = sys_get_temp_dir() . '/exports-symlink-test-' . uniqid();
        mkdir($tempDir . '/docs', 0755, true);

        // Create a secret file OUTSIDE the input directory
        $secretFile = $tempDir . '/secret.txt';
        file_put_contents($secretFile, 'SECRET DATA via symlink - should not be accessible');

        // Create a symlink INSIDE the docs directory pointing to the secret file
        $symlinkPath = $tempDir . '/docs/linked.rst';

        // Skip test if symlinks not supported
        if (!@symlink($secretFile, $symlinkPath)) {
            self::markTestSkipped('Symlinks not supported on this system');
        }

        try {
            // Verify symlink was created
            self::assertTrue(is_link($symlinkPath));

            // Set input dir to 'docs'
            $this->buildState->setInputDir($tempDir . '/docs');

            // Try to access the symlink
            $document = new DocumentNode('hash1', 'linked');
            $context = new CompilerContext(new ProjectNode());
            $this->pass->run([$document], $context);

            $exports = $this->buildState->getExports('linked');
            self::assertNotNull($exports);

            // The realpath() check in findSourceFile() resolves symlinks, so
            // the symlink target (/tmp/.../secret.txt) is outside the input dir
            // and should be rejected. The content hash should be from document
            // serialization (fallback), NOT from the secret file.
            $secretFileHash = $this->hasher->hashFile($secretFile);
            self::assertNotSame($secretFileHash, $exports->contentHash);
        } finally {
            if (is_link($symlinkPath)) {
                unlink($symlinkPath);
            }

            if (file_exists($secretFile)) {
                unlink($secretFile);
            }

            rmdir($tempDir . '/docs');
            rmdir($tempDir);
        }
    }
}
