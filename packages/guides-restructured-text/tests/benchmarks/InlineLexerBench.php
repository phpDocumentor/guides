<?php

declare(strict_types=1);

namespace phpDocumentor\Guides\RestructuredText;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use phpDocumentor\Guides\RestructuredText\Parser\InlineLexer;

final class InlineLexerBench
{
    #[Revs([1000, 10_000])]
    #[Iterations(5)]
    public function benchInlineLexer(): void
    {
        $lexer = new InlineLexer();
        $lexer->setInput('This is a `link`_ to a section.');
    }
}
