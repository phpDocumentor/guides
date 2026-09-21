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

use phpDocumentor\Guides\Nodes\Language;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\Nodes\TextDirection;
use phpDocumentor\Guides\RestructuredText\Directives\Attributes\Option;
use phpDocumentor\Guides\RestructuredText\Nodes\ContainerNode;
use phpDocumentor\Guides\RestructuredText\Nodes\DirectiveNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\Rule;
use Psr\Log\LoggerInterface;

use function preg_match;
use function sprintf;

/**
 * Divs a sub document in a div with a given class or set of classes.
 *
 * Also accepts `:lang:` and `:dir:` options, to mark up a block written in
 * another language or in a right-to-left script:
 *
 * .. container::
 *     :lang: ar
 *     :dir: rtl
 *
 *     Some content in Arabic.
 *
 * @link https://docutils.sourceforge.io/docs/ref/rst/directives.html#container
 */
#[Attributes\Directive(name: 'container', aliases: ['div'])]
#[Option(name: 'lang', description: 'Sets the HTML lang attribute on the wrapping div, e.g. "en", "nb", "ar".')]
#[Option(name: 'dir', description: 'Sets the HTML dir attribute on the wrapping div: "ltr", "rtl", or "auto".')]
final class ContainerDirective extends SubDirective
{
    public function __construct(
        protected Rule $startingRule,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($startingRule);
    }

    public function createNode(DirectiveNode $directiveNode): Node
    {
        $directive = $directiveNode->getDirective();

        $options = ['class' => $directive->getData()];
        if ($directive->hasOption('lang')) {
            $language = $directive->getOptionString('lang');
            if (preg_match(Language::PATTERN, $language) !== 1) {
                $this->logger->warning(
                    sprintf(
                        'The "lang" option of the "%s" directive expects a BCP 47 language tag (e.g. "en", "en-US"), but was given "%s".',
                        $directive->getName(),
                        $language,
                    ),
                    $directiveNode->getSourceLocation()->toLoggerInformation(),
                );
            }

            $options['lang'] = $language;
        }

        if ($directive->hasOption('dir')) {
            $written = $directive->getOptionString('dir');
            $direction = TextDirection::tryFromUserInput($written);
            if ($direction === null) {
                $this->logger->warning(
                    sprintf(
                        'The "dir" option of the "%s" directive expects one of "ltr", "rtl" or "auto", but was given "%s".',
                        $directive->getName(),
                        $written,
                    ),
                    $directiveNode->getSourceLocation()->toLoggerInformation(),
                );

                $direction = TextDirection::Auto;
            }

            $options['dir'] = $direction->value;
        }

        return (new ContainerNode($directiveNode->getChildren()))
            ->withOptions($options);
    }
}
