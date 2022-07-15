<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText;

use phpDocumentor\Guides\MarkupLanguageParser as ParserInterface;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\Directives\AdmonitionDirective;
use phpDocumentor\Guides\RestructuredText\Directives\BestPracticeDirective;
use phpDocumentor\Guides\RestructuredText\Directives\CautionDirective;
use phpDocumentor\Guides\RestructuredText\Directives\ClassDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Code;
use phpDocumentor\Guides\RestructuredText\Directives\CodeBlock;
use phpDocumentor\Guides\RestructuredText\Directives\ContainerDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Directive;
use phpDocumentor\Guides\RestructuredText\Directives\Figure;
use phpDocumentor\Guides\RestructuredText\Directives\HintDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Image;
use phpDocumentor\Guides\RestructuredText\Directives\ImportantDirective;
use phpDocumentor\Guides\RestructuredText\Directives\IncludeDirective;
use phpDocumentor\Guides\RestructuredText\Directives\IndexDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Meta;
use phpDocumentor\Guides\RestructuredText\Directives\NoteDirective;
use phpDocumentor\Guides\RestructuredText\Directives\RawDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Replace;
use phpDocumentor\Guides\RestructuredText\Directives\RoleDirective;
use phpDocumentor\Guides\RestructuredText\Directives\SeeAlsoDirective;
use phpDocumentor\Guides\RestructuredText\Directives\SidebarDirective;
use phpDocumentor\Guides\RestructuredText\Directives\TipDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Title;
use phpDocumentor\Guides\RestructuredText\Directives\Toctree;
use phpDocumentor\Guides\RestructuredText\Directives\TopicDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Uml;
use phpDocumentor\Guides\RestructuredText\Directives\WarningDirective;
use phpDocumentor\Guides\RestructuredText\Directives\Wrap;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DocumentRule;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use phpDocumentor\Guides\RestructuredText\Span\SpanParser;
use phpDocumentor\Guides\RestructuredText\Toc\GlobSearcher;
use phpDocumentor\Guides\RestructuredText\Toc\ToctreeBuilder;
use phpDocumentor\Guides\UrlGenerator;
use RuntimeException;

use function strtolower;

class MarkupLanguageParser implements ParserInterface
{
    private ?ParserContext $environment = null;

    /** @var Directive[] */
    private array $directives = [];

    private ?string $filename = null;

    private ?DocumentParserContext $documentParser = null;
    private Rule $startingRule;

    /**
     * @param iterable<Directive> $directives
     */
    public function __construct(
        Rule $startingRule,
        iterable $directives
    ) {
        foreach ($directives as $directive) {
            $this->registerDirective($directive);
        }
        $this->startingRule = $startingRule;
    }

    public static function createInstance(): self
    {
        $spanParser = new SpanParser();

        $directives = [
            new AdmonitionDirective($spanParser),
            new BestPracticeDirective($spanParser),
            new CautionDirective($spanParser),
            new ClassDirective(),
            new Code(),
            new CodeBlock(),
            new ContainerDirective(),
            new Figure(new UrlGenerator()),
            new HintDirective($spanParser),
            new Image(new UrlGenerator()),
            new ImportantDirective($spanParser),
            new IncludeDirective(),
            new IndexDirective(),
            new Meta(),
            new NoteDirective($spanParser),
            new RawDirective(),
            new Replace($spanParser),
            new RoleDirective(),
            new SeeAlsoDirective($spanParser),
            new SidebarDirective(),
            new TipDirective($spanParser),
            new Title(),
            new Toctree(
                new ToctreeBuilder(
                    new GlobSearcher(new UrlGenerator()),
                    new UrlGenerator()
                )
            ),
            new TopicDirective(),
            new Uml(),
            new WarningDirective($spanParser),
            new Wrap(),
        ];

        $documentRule = new DocumentRule($directives);


        return new self($documentRule, $directives);
    }

    public function supports(string $inputFormat): bool
    {
        return strtolower($inputFormat) === 'rst';
    }

    public function getSubParser(): MarkupLanguageParser
    {
        return new MarkupLanguageParser(
            $this->startingRule,
            $this->directives
        );
    }

    public function getEnvironment(): ParserContext
    {
        if ($this->environment === null) {
            throw new RuntimeException(
                'A parser\'s Environment should not be consulted before parsing has started'
            );
        }

        return $this->environment;
    }

    private function registerDirective(Directive $directive): void
    {
        $this->directives[$directive->getName()] = $directive;
        foreach ($directive->getAliases() as $alias) {
            $this->directives[$alias] = $directive;
        }
    }

    public function getDocument(): DocumentNode
    {
        if ($this->documentParser === null) {
            throw new RuntimeException('Nothing has been parsed yet.');
        }

        return $this->documentParser->getDocument();
    }

    public function getFilename(): string
    {
        return $this->filename ?: '(unknown)';
    }

    public function parse(ParserContext $environment, string $contents): DocumentNode
    {
        $this->environment = $environment;

        $documentContext = new DocumentParserContext($contents, $environment, $this);

        if ($this->startingRule->applies($documentContext)) {
            return $this->startingRule->apply($documentContext);
        }

        throw new \InvalidArgumentException('Content is not a valid document content');
    }

    /**
     * @deprecated this should be replaced by proper usage of productions in other productions, by now this is a hack.
     */
    public function parseFragment(DocumentParserContext $documentParserContext, string $contents): DocumentNode
    {
        $documentParserContext = $documentParserContext->withContents($contents);
        if ($this->startingRule->applies($documentParserContext)) {
            return $this->startingRule->apply($documentParserContext);
        }

        throw new \InvalidArgumentException('Content is not a valid document content');
    }
}
