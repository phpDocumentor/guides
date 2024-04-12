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

namespace phpDocumentor\Guides\Compiler;

use Doctrine\Deprecations\PHPUnit\VerifyDeprecations;
use phpDocumentor\Guides\Nodes\ProjectNode;
use PHPUnit\Framework\TestCase;

final class CompilerContextTest extends TestCase
{
    use VerifyDeprecations;

    public function testTriggersDeprecationOnContextExtend(): void
    {
        $this->expectDeprecationWithIdentifier('https://github.com/phpDocumentor/guides/issues/971');
        $context = new class (new ProjectNode()) extends CompilerContext{
        };
    }

    public function testNoDeprecationOnNormalConstruct(): void
    {
        $this->expectNoDeprecationWithIdentifier('https://github.com/phpDocumentor/guides/issues/971');
        $context = new CompilerContext(new ProjectNode());
    }
}
