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

use phpDocumentor\Guides\Nodes\DocumentNode;

interface MarkupLanguageParser
{
    public function supports(string $inputFormat): bool;

    public function getParserContext(): ParserContext;

    public function parse(ParserContext $parserContext, string $contents): DocumentNode;

    public function getDocument(): DocumentNode;
}
