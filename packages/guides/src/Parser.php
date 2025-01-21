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

use League\Flysystem\FilesystemInterface;
use phpDocumentor\FileSystem\FileSystem as FileSystemAlias;
use phpDocumentor\FileSystem\FlySystemAdapter;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\ReferenceResolvers\DocumentNameResolverInterface;
use RuntimeException;
use Webmozart\Assert\Assert;

use function getcwd;

/**
 * Determines the correct markup language parser to use based on the input and output format and with it, and parses
 * the file contents.
 */
final class Parser
{
    private ParserContext|null $parserContext = null;

    /** @var MarkupLanguageParser[] */
    private array $parserStrategies = [];

    /** @param iterable<MarkupLanguageParser> $parserStrategies */
    public function __construct(
        private readonly DocumentNameResolverInterface $documentNameResolver,
        iterable $parserStrategies,
    ) {
        foreach ($parserStrategies as $strategy) {
            $this->registerStrategy($strategy);
        }
    }

    public function registerStrategy(MarkupLanguageParser $strategy): void
    {
        $this->parserStrategies[] = $strategy;
    }

    /** @psalm-assert ParserContext $this->parserContext */
    public function prepare(
        FilesystemInterface|FileSystemAlias|null $origin,
        string $sourcePath,
        string $fileName,
        ProjectNode $projectNode,
        int $initialHeaderLevel = 1,
    ): void {
        if ($origin === null) {
            $cwd = getcwd();
            Assert::string($cwd);
            $origin = FlySystemAdapter::createForPath($cwd);
        }

        $this->parserContext = $this->createParserContext(
            $sourcePath,
            $fileName,
            $origin,
            $initialHeaderLevel,
            $projectNode,
        );
    }

    public function parse(
        string $text,
        string $inputFormat = 'rst',
    ): DocumentNode {
        if ($this->parserContext === null) {
            // Environment is not set; then the prepare method hasn't been called and we consider
            // this a one-off parse of dynamic RST content.
            $this->prepare(null, '', 'index', new ProjectNode());
        }

        $parser = $this->determineParser($inputFormat);

        $document = $parser->parse($this->parserContext, $text);

        $this->parserContext = null;

        return $document;
    }

    private function determineParser(string $fileExtension): MarkupLanguageParser
    {
        foreach ($this->parserStrategies as $parserStrategy) {
            if ($parserStrategy->supports($fileExtension)) {
                return $parserStrategy;
            }
        }

        throw new RuntimeException('Unable to parse document, no matching parsing strategy could be found');
    }

    private function createParserContext(
        string $sourcePath,
        string $file,
        FilesystemInterface|FileSystemAlias $origin,
        int $initialHeaderLevel,
        ProjectNode $projectNode,
    ): ParserContext {
        return new ParserContext(
            $projectNode,
            $file,
            $sourcePath,
            $initialHeaderLevel,
            $origin,
            $this->documentNameResolver,
        );
    }
}
