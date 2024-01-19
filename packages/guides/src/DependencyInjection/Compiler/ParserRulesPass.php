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

namespace phpDocumentor\Guides\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ParserRulesPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $body = $container->findDefinition('phpdoc.guides.parser.rst.body_elements');
        $structual = $container->findDefinition('phpdoc.guides.parser.rst.structural_elements');

        foreach ($this->findAndSortTaggedServices('phpdoc.guides.parser.rst.structural_element', $container) as $reference) {
            $structual->addMethodCall('push', [$reference]);
        }

        foreach ($this->findAndSortTaggedServices('phpdoc.guides.parser.rst.body_element', $container) as $reference) {
            $body->addMethodCall('push', [$reference]);
            //TODO: remove this call to $structual, body elements should not be part of it once subparser is removed
            $structual->addMethodCall('push', [$reference]);
        }
    }
}
