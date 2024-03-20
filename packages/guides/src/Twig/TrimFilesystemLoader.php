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

namespace phpDocumentor\Guides\Twig;

use Twig\Loader\FilesystemLoader;
use Twig\Source;

use function preg_replace;

/**
 * A file system loader that trims the last line ending from the template
 * content.
 */
class TrimFilesystemLoader extends FilesystemLoader
{
    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

    /** @param string $name */
    public function getSourceContext($name): Source
    {
        // phpcs:enable
        $source = parent::getSourceContext($name);

        return new Source(
            preg_replace('/\R$/', '', $source->getCode()) ?? $source->getCode(),
            $source->getName(),
            $source->getPath(),
        );
    }
}
