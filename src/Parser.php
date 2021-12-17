<?php

declare(strict_types=1);

namespace phpDocumentor\Guides;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use phpDocumentor\Guides\Nodes\DocumentNode;
use RuntimeException;

use function filemtime;
use function getcwd;
use function ltrim;
use function sprintf;
use function trim;

/**
 * Determines the correct markup language parser to use based on the input and output format and with it, and parses
 * the file contents.
 */
final class Parser
{
    /** @var ?ParserContext */
    private $parserContext = null;

    /** @var ?Metas */
    private $metas = null;


    /** @var UrlGenerator */
    private $urlGenerator;

    /** @var MarkupLanguageParser[] */
    private $parserStrategies = [];

    /**
     * @param iterable<MarkupLanguageParser> $parserStrategies
     */
    public function __construct(
        UrlGenerator $urlGenerator,
        iterable $parserStrategies
    ) {
        $this->urlGenerator = $urlGenerator;

        foreach ($parserStrategies as $strategy) {
            $this->registerStrategy($strategy);
        }
    }

    public function registerStrategy(MarkupLanguageParser $strategy): void
    {
        $this->parserStrategies[] = $strategy;
    }

    public function prepare(
        Metas $metas,
        ?FilesystemInterface $origin,
        string $sourcePath,
        string $destinationPath,
        string $fileName,
        int $initialHeaderLevel = 1
    ): void {
        if ($origin === null) {
            $origin = new Filesystem(new Local(getcwd()));
        }

        $this->metas = $metas;
        $this->parserContext = $this->createParserContext(
            $sourcePath,
            $fileName,
            $origin,
            $destinationPath,
            $initialHeaderLevel
        );
    }

    /**
     * @todo Investigate if we can somehow dump the output format bit; the AST should not depend on the
     *       expected rendering.
     */
    public function parse(
        string $text,
        string $inputFormat = 'rst',
        string $outputFormat = 'html'
    ): DocumentNode {
        if ($this->metas === null || $this->parserContext === null) {
            // if Metas or Environment is not set; then the prepare method hasn't been called and we consider
            // this a one-off parse of dynamic RST content.
            $this->prepare(new Metas(), null, '', '', 'index');
        }

        $this->parserContext->setCurrentAbsolutePath(
            $this->buildPathOnFileSystem(
                $this->parserContext->getCurrentFileName(),
                $this->parserContext->getCurrentDirectory(),
                $inputFormat
            )
        );

        $parser = $this->determineParser($inputFormat);

        $this->parserContext->reset();

        $document = $parser->parse($this->parserContext, $text);
        $document->setVariables($this->parserContext->getVariables());
        $this->addDocumentToMetas($this->parserContext->getDestinationPath(), $outputFormat, $document);

        $this->metas         = null;
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
        FilesystemInterface $origin,
        string $destinationPath,
        int $initialHeaderLevel
    ): ParserContext {
        return new ParserContext(
            $file,
            $sourcePath,
            $destinationPath,
            $initialHeaderLevel,
            $origin,
            $this->urlGenerator
        );
    }

    private function buildPathOnFileSystem(string $file, string $currentDirectory, string $extension): string
    {
        return ltrim(sprintf('%s/%s.%s', trim($currentDirectory, '/'), $file, $extension), '/');
    }

    /**
     * @return array<array<string|null>>
     */
    private function compileTableOfContents(DocumentNode $document, ParserContext $parserContext): array
    {
        $result = [];
        $nodes = $document->getTocs();
        foreach ($nodes as $toc) {
            $files = $toc->getFiles();

            foreach ($files as $key => $file) {
                $files[$key] =  $this->urlGenerator->canonicalUrl($parserContext->getDirName(), $file);
            }

            $result[] = $files;
        }

        return $result;
    }

    private function buildDocumentUrl(ParserContext $parserContext, string $extension): string
    {
        return $parserContext->getUrl() . '.' . $extension;
    }

    private function buildOutputUrl(string $destinationPath, string $outputFormat, ParserContext $parserContext): string
    {
        $outputFolder = $destinationPath ? $destinationPath . '/' : '';

        return $outputFolder . $this->buildDocumentUrl($parserContext, $outputFormat);
    }

    private function addDocumentToMetas(string $destinationPath, string $outputFormat, DocumentNode $document): void
    {
        $this->metas->set(
            $this->parserContext->getCurrentFileName(),
            $this->buildOutputUrl($destinationPath, $outputFormat, $this->parserContext),
            $document->getTitle() ? $document->getTitle()->getValueString() : '',
            $document->getTitles(),
            $this->compileTableOfContents($document, $this->parserContext),
            (int) filemtime($this->parserContext->getCurrentAbsolutePath()),
            $document->getDependencies(),
            $this->parserContext->getLinks()
        );
    }
}
