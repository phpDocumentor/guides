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

        // convertDomElementToArray() below runs phpize() on every attribute value, which turns
        // "0.10" into 0.1 and "1.0" into 1. The <project> attributes are all strings, so they are
        // read from the DOM and the element is detached before that call.
        $projectConfig = null;
        $project = $this->firstChildElement($element, 'project');
        if ($project !== null) {
            $projectConfig = [];
            foreach ($project->attributes as $attribute) {
                if (!($attribute instanceof DOMAttr)) {
                    continue;
                }

                $value = $attribute->value;

                // Files that adopted the version="'3.0'" workaround against the old phpize()
                // call must keep rendering 3.0; the quotes are needed nowhere else.
                if ($attribute->name === 'version' || $attribute->name === 'release') {
                    $value = trim($value, "'");
                }

                $projectConfig[$attribute->name] = $value;
            }

            $project->parentNode?->removeChild($project);
        }

        // A file whose only child was <project> leaves an empty root here, for which
        // convertDomElementToArray() returns null.
        $rootConfig = XmlUtils::convertDomElementToArray($element);
        if (!is_array($rootConfig)) {
            $rootConfig = [];
        }

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

    /**
     * Returns the first DIRECT child of the given name. A subtree search would also find a <project>
     * nested in an <extension>, which the schema allows, and take it for the project configuration.
     */
    private function firstChildElement(DOMElement $element, string $name): DOMElement|null
    {
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === $name) {
                return $child;
            }
        }

        return null;
    }

    public function supports(mixed $resource, string|null $type = null): bool
    {
        return $type === 'xml' && is_string($resource);
    }
}
