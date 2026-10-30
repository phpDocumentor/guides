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

namespace phpDocumentor\Guides\RestructuredText\Directives;

use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\CollectionNode;
use phpDocumentor\Guides\Nodes\LiteralBlockNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Directives\OptionMapper\CodeNodeOptionMapper;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DocumentRule;
use RuntimeException;

use function array_key_exists;
use function array_slice;
use function count;
use function explode;
use function implode;
use function max;
use function preg_split;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function substr;

#[Option(name: 'literal', description: 'If set, the contents will be rendered as a literal block.')]
#[Option(name: 'code', description: 'If set, the contents will be rendered as a code block with the specified language.')]
final class IncludeDirective extends BaseDirective
{
    public function __construct(
        private readonly DocumentRule $startingRule,
        private readonly CodeNodeOptionMapper|null $codeNodeOptionMapper = null,
    ) {
    }

    public function getName(): string
    {
        return 'include';
    }

    /** {@inheritDoc} */
    public function processNode(
        BlockContext $blockContext,
        Directive $directive,
    ): Node {
        $parserContext = $blockContext->getDocumentParserContext()->getParser()->getParserContext();
        $path = $parserContext->absoluteRelativePath($directive->getData());

        $origin = $parserContext->getOrigin();
        if (!$origin->has($path)) {
            throw new RuntimeException(
                sprintf('Include "%s" (%s) does not exist or is not readable.', $directive->getData(), $path),
            );
        }

        $contents = $origin->read($path);

        if ($contents === false) {
            throw new RuntimeException(sprintf('Could not load file from path %s', $path));
        }

        $contents = $this->selectContents($contents, $directive);

        if (array_key_exists('literal', $directive->getOptions())) {
            $contents = str_replace("\r\n", "\n", $contents);

            if (array_key_exists('number-lines', $directive->getOptions())) {
                $codeNode = new CodeNode(explode("\n", $contents));
                $this->codeNodeOptionMapper?->apply($codeNode, $directive->getOptions(), $blockContext);

                return $codeNode;
            }

            return new LiteralBlockNode($contents);
        }

        if (array_key_exists('code', $directive->getOptions())) {
            $contents = str_replace("\r\n", "\n", $contents);
            $codeNode = new CodeNode(
                explode("\n", $contents),
            );
            $codeNode->setLanguage((string) $directive->getOption('code')->getValue());
            $this->codeNodeOptionMapper?->apply($codeNode, $directive->getOptions(), $blockContext);

            return $codeNode;
        }

        $currentDocument = $blockContext->getDocumentParserContext()->getDocument();
        $currentFileName = $parserContext->getCurrentFileName();
        $parserContext->setCurrentFileName($parserContext->relativeFileName($directive->getData()));

        try {
            $subContext = new BlockContext($blockContext->getDocumentParserContext(), $contents);
            $document = $this->startingRule->apply($subContext);
        } finally {
            $parserContext->setCurrentFileName($currentFileName);
            $blockContext->getDocumentParserContext()->setDocument($currentDocument);
        }

        foreach ($document->getVariables() as $name => $value) {
            $currentDocument->addVariable($name, $value);
        }

        return new CollectionNode($document->getChildren());
    }

    private function selectContents(string $contents, Directive $directive): string
    {
        if ($directive->hasOption('start-line') || $directive->hasOption('end-line')) {
            $start = $directive->hasOption('start-line') ? $directive->getOptionInt('start-line') : 0;
            $end = $directive->hasOption('end-line')
                ? $directive->getOptionInt('end-line')
                : null;

            $contents = $this->selectLines($contents, $start, $end);
        }

        if ($directive->hasOption('start-after')) {
            $marker = $directive->getOptionString('start-after');
            if ($marker === '') {
                $marker = "\n\n";
            }

            $position = strpos($contents, $marker);
            if ($position === false) {
                throw new RuntimeException('Text not found for option ":start-after:".');
            }

            $contents = substr($contents, $position + strlen($marker));
        }

        if ($directive->hasOption('end-before')) {
            $marker = $directive->getOptionString('end-before');
            if ($marker === '') {
                $marker = "\n\n";
            }

            $position = strpos($contents, $marker);
            if ($position === false) {
                throw new RuntimeException('Text not found for option ":end-before:".');
            }

            $contents = substr($contents, 0, $position);
        }

        return $contents;
    }

    private function selectLines(string $contents, int $start, int|null $end): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $contents);
        if ($lines === false) {
            return '';
        }

        return implode("\n", array_slice($lines, $start, max(0, ($end ?? count($lines)) - $start)));
    }
}
