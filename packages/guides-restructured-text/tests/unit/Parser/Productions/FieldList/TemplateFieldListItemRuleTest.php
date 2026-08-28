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
use phpDocumentor\Guides\RestructuredText\Parser\Productions\RuleTestCase;

final class TemplateFieldListItemRuleTest extends RuleTestCase
{
    private TemplateFieldListItemRule $rule;

    protected function setUp(): void
    {
        $this->rule = new TemplateFieldListItemRule();
    }

    public function test_it_applies_to_a_template_field_regardless_of_case(): void
    {
        self::assertTrue($this->rule->applies(new FieldListItemNode('template', 'genindex')));
        self::assertTrue($this->rule->applies(new FieldListItemNode('Template', 'genindex')));
        self::assertFalse($this->rule->applies(new FieldListItemNode('orphan', '')));
    }
}
