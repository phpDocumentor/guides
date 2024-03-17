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

use phpDocumentor\Guides\RestructuredText\Parser\BlockContext;
use phpDocumentor\Guides\RestructuredText\Parser\Directive;
use Psr\Log\LoggerInterface;

/**
 * This directive is deactivated for security reasons. If you need it in your project, you must implement it yourself.
 */
final class RawDirective extends ActionDirective
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return 'raw';
    }

    public function processAction(BlockContext $blockContext, Directive $directive): void
    {
        $this->logger->error('The raw directive is not supported for security reasons. ', $blockContext->getLoggerInformation());
    }
}
