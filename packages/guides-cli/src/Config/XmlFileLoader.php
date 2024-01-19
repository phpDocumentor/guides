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

namespace phpDocumentor\Guides\Cli\Config;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Util\Exception\XmlParsingException;
use Symfony\Component\Config\Util\XmlUtils;

use function array_merge;
use function assert;
use function is_array;
use function is_string;
use function sprintf;

class XmlFileLoader extends FileLoader
{
    /** @return mixed[][] */
    public function load(mixed $resource, string|null $type = null): array
    {
        assert(is_string($resource));

        $document = XmlUtils::loadFile($resource);
        $element = $document->documentElement;
        if ($element === null) {
            throw new XmlParsingException(sprintf('The XML file "%s" is not valid.', $resource));
        }

        $rootConfig = XmlUtils::convertDomElementToArray($element);
        assert(is_array($rootConfig));

        $configs = [];
        if (isset($rootConfig['import'])) {
            foreach ((array) $rootConfig['import'] as $import) {
                $config = $this->import($import, 'xml');
                assert(is_array($config));

                $configs = array_merge($configs, $config);
            }
        }

        unset($rootConfig['import']);

        $configs[] = $rootConfig;

        return $configs;
    }

    public function supports(mixed $resource, string|null $type = null): bool
    {
        return $type === 'xml' && is_string($resource);
    }
}
