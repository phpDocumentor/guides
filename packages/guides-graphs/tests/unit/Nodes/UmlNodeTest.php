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

namespace phpDocumentor\Guides\Graphs\Nodes;

use PHPUnit\Framework\TestCase;

final class UmlNodeTest extends TestCase
{
    public function test_it_can_be_created_with_a_value(): void
    {
        $node = new UmlNode('value');

        self::assertSame('value', $node->getValue());
    }

    public function test_you_can_set_a_caption_for_underneath_diagrams(): void
    {
        $caption = 'caption';

        $node = new UmlNode('value');
        $node->setCaption($caption);

        self::assertSame($caption, $node->getCaption());
    }

    public function test_you_can_pass_classes_for_in_templates(): void
    {
        $classes = ['float-left', 'my-class'];

        $node = new UmlNode('value');
        $node->setClasses($classes);

        self::assertSame($classes, $node->getClasses());
        self::assertSame('float-left my-class', $node->getClassesString());
    }
}
