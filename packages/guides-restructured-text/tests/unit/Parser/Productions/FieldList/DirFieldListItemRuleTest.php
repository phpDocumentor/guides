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
use phpDocumentor\Guides\Nodes\Metadata\DirectionNode;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\RuleTestCase;

final class DirFieldListItemRuleTest extends RuleTestCase
{
    private TestHandler $logHandler;
    private DirFieldListItemRule $rule;

    protected function setUp(): void
    {
        $this->logHandler = new TestHandler();
        $logger = new Logger('test');
        $logger->pushHandler($this->logHandler);
        $this->rule = new DirFieldListItemRule($logger);
    }

    public function test_it_applies_to_a_dir_field_regardless_of_case(): void
    {
        self::assertTrue($this->rule->applies(new FieldListItemNode('dir', 'rtl')));
        self::assertTrue($this->rule->applies(new FieldListItemNode('Dir', 'rtl')));
        self::assertFalse($this->rule->applies(new FieldListItemNode('orphan', '')));
    }

    public function test_it_creates_a_direction_node_with_a_valid_value(): void
    {
        $node = $this->rule->apply(new FieldListItemNode('dir', 'rtl'), $this->createContext(''));

        self::assertInstanceOf(DirectionNode::class, $node);
        self::assertSame('rtl', $node->getValue());
        self::assertFalse($this->logHandler->hasWarningRecords());
    }

    public function test_invalid_direction_warns_but_is_still_applied(): void
    {
        $node = $this->rule->apply(new FieldListItemNode('dir', 'sideways'), $this->createContext(''));

        self::assertInstanceOf(DirectionNode::class, $node);
        self::assertSame('sideways', $node->getValue());
        self::assertTrue($this->logHandler->hasWarningThatContains(
            'expects one of "ltr", "rtl" or "auto", but was given "sideways"',
        ));
    }
}
