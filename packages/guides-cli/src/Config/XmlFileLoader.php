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

use DOMAttr;
use DOMElement;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Util\Exception\XmlParsingException;
use Symfony\Component\Config\Util\XmlUtils;

use function array_merge;
use function assert;
use function is_array;
use function is_string;
use function sprintf;
use function trim;

final class XmlFileLoader extends FileLoader
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

        // The <project> attributes (title, version, release, copyright) are all
        // strings and are read from the DOM directly. XmlUtils::convertDomElementToArray()
        // below runs phpize() on every attribute value, which coerces version-like
        // strings into numbers ("0.10" would become the float 0.1, "1.0" would
        // become 1). Reading them straight from the DOM and detaching <project>
        // beforehand keeps the version exactly as written.
        $projectConfig = null;
        $project = $element->getElementsByTagName('project')->item(0);
        if ($project instanceof DOMElement) {
            $projectConfig = [];
            foreach ($project->attributes as $attribute) {
                if (!($attribute instanceof DOMAttr)) {
                    continue;
                }

                $value = $attribute->value;

                // Backward compatibility: to stop the previous phpize() call from
                // turning a version into a number, consumers wrapped it in single
                // quotes (e.g. version="'3.0'"). The value is now read straight from
                // the DOM so the quotes are no longer needed, but existing guides.xml
                // files may still contain them; strip them for these two attributes.
                if ($attribute->name === 'version' || $attribute->name === 'release') {
                    $value = trim($value, "'");
                }

                $projectConfig[$attribute->name] = $value;
            }

            $project->parentNode?->removeChild($project);
        }

        $rootConfig = XmlUtils::convertDomElementToArray($element);
        assert(is_array($rootConfig));

        if ($projectConfig !== null) {
            $rootConfig['project'] = $projectConfig;
        }

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
