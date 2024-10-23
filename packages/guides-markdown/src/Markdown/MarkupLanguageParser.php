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

namespace phpDocumentor\Guides\Markdown;

use League\CommonMark\Environment\Environment as CommonMarkEnvironment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\NodeWalker;
use League\CommonMark\Parser\MarkdownParser;
use phpDocumentor\Guides\MarkupLanguageParser as MarkupLanguageParserInterface;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\Settings\ProjectSettings;
use phpDocumentor\Guides\Settings\SettingsManager;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function ltrim;
use function md5;
use function sprintf;
use function strtolower;

final class MarkupLanguageParser implements MarkupLanguageParserInterface
{
    private readonly MarkdownParser $markdownParser;

    private ParserContext|null $parserContext = null;

    private DocumentNode|null $document = null;

    private SettingsManager $settingsManager;

    /** @param iterable<ParserInterface<Node>> $parsers */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly iterable $parsers,
        SettingsManager|null $settingsManager,
    ) {
        $cmEnvironment = new CommonMarkEnvironment(['html_input' => 'strip']);
        $cmEnvironment->addExtension(new CommonMarkCoreExtension());
        $cmEnvironment->addExtension(new TableExtension());
        $cmEnvironment->addExtension(new AutolinkExtension());
        $cmEnvironment->addExtension(new FrontMatterExtension(new SymfonyYamlFrontMatterParser()));
        $this->markdownParser = new MarkdownParser($cmEnvironment);
        // if for backward compatibility reasons no settings manager was passed, use the defaults
        $this->settingsManager = $settingsManager ?? new SettingsManager(new ProjectSettings());
    }

    public function supports(string $inputFormat): bool
    {
        return strtolower($inputFormat) === 'md';
    }

    public function parse(ParserContext $parserContext, string $contents): DocumentNode
    {
        $this->parserContext = $parserContext;

        $ast = $this->markdownParser->parse($contents);

        return $this->parseDocument($ast->walker(), md5($contents));
    }

    private function parseDocument(NodeWalker $walker, string $hash): DocumentNode
    {
        $document = new DocumentNode($hash, ltrim($this->getParserContext()->getCurrentAbsolutePath(), '/'));
        $document->setOrphan(!$this->settingsManager->getProjectSettings()->isAutomaticMenu());
        $this->document = $document;

        while ($event = $walker->next()) {
            $commonMarkNode = $event->getNode();

            if ($event->isEntering()) {
                // Use entering events for context switching
                foreach ($this->parsers as $parser) {
                    if ($parser->supports($event)) {
                        $document->addChildNode($parser->parse($this, $walker, $commonMarkNode));
                        break;
                    }
                }

                continue;
            }

            if ($commonMarkNode instanceof Document) {
                return $document;
            }

            $this->logger->warning(sprintf('"%s" node is not yet supported in context %s. ', $commonMarkNode::class, 'Document'));
        }

        return $document;
    }

    public function getParserContext(): ParserContext
    {
        if ($this->parserContext === null) {
            throw new RuntimeException(
                'A parser\'s Environment should not be consulted before parsing has started',
            );
        }

        return $this->parserContext;
    }

    public function getDocument(): DocumentNode
    {
        if ($this->document === null) {
            throw new RuntimeException('Cannot get document as parser is not started');
        }

        return $this->document;
    }
}
