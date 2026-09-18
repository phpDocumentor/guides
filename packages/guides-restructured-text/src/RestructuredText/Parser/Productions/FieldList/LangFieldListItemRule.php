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

namespace phpDocumentor\Guides\RestructuredText\Parser\Productions\FieldList;

use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\LanguageNode;
use phpDocumentor\Guides\Nodes\Metadata\MetadataNode;
use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use Psr\Log\LoggerInterface;

use function preg_match;
use function sprintf;
use function strtolower;

final class LangFieldListItemRule implements FieldListItemRule
{
    private const LANGUAGE_TAG_PATTERN = '/^[a-zA-Z]{2,8}(-[a-zA-Z0-9]{1,8})*$/';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function applies(FieldListItemNode $fieldListItemNode): bool
    {
        return strtolower($fieldListItemNode->getTerm()) === 'lang';
    }

    public function apply(FieldListItemNode $fieldListItemNode, BlockContext $blockContext): MetadataNode
    {
        $language = $fieldListItemNode->getPlaintextContent();
        if (preg_match(self::LANGUAGE_TAG_PATTERN, $language) !== 1) {
            $this->logger->warning(
                sprintf(
                    'The "lang" field expects a BCP 47 language tag (e.g. "en", "en-US"), but was given "%s".',
                    $language,
                ),
                $blockContext->getLoggerInformation(),
            );
        }

        return new LanguageNode($language);
    }
}
