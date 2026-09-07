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

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use phpDocumentor\Guides\Nodes\FieldLists\FieldListItemNode;
use phpDocumentor\Guides\Nodes\Metadata\LanguageNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\RuleTestCase;

final class LangFieldListItemRuleTest extends RuleTestCase
{
    private TestHandler $logHandler;
    private LangFieldListItemRule $rule;

    protected function setUp(): void
    {
        $this->logHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($this->logHandler);
        $this->rule = new LangFieldListItemRule($logger);
    }

    public function test_it_applies_to_a_lang_field_regardless_of_case(): void
    {
        self::assertTrue($this->rule->applies(new FieldListItemNode('lang', 'ar')));
        self::assertTrue($this->rule->applies(new FieldListItemNode('Lang', 'ar')));
        self::assertFalse($this->rule->applies(new FieldListItemNode('orphan', '')));
    }

    public function test_it_creates_a_language_node_with_the_given_value(): void
    {
        $node = $this->rule->apply(new FieldListItemNode('lang', 'ar'), $this->createContext(''));

        self::assertInstanceOf(LanguageNode::class, $node);
        self::assertSame('ar', $node->getValue());
        self::assertFalse($this->logHandler->hasWarningRecords());
    }

    public function test_invalid_language_warns_but_is_still_applied(): void
    {
        $node = $this->rule->apply(new FieldListItemNode('lang', 'not a language'), $this->createContext(''));

        self::assertInstanceOf(LanguageNode::class, $node);
        self::assertSame('not a language', $node->getValue());
        self::assertTrue($this->logHandler->hasWarningThatContains(
            'expects a BCP 47 language tag (e.g. "en", "en-US"), but was given "not a language"',
        ));
    }
}
