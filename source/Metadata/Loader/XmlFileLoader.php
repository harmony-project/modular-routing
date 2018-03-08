<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Metadata\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * Loads modular routing metadata files formatted in XML.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class XmlFileLoader extends FileLoader
{
    /**
     * Loads an XML file.
     *
     * @param string      $file An XML file path
     * @param string|null $type The resource type
     *
     * @return array                     A collection of metadata
     * @throws \InvalidArgumentException When the file cannot be loaded or when the XML cannot be
     *                                   parsed because it does not validate against the scheme.
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $xml = $this->loadFile($path);

        $collection = [];

        // process routes and imports
        foreach ($xml->documentElement->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            $this->parseNode($collection, $node, $path, $file);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'xml' === $type);
    }

    /**
     * Loads an XML file.
     *
     * @param string $file An XML file path
     *
     * @return \DOMDocument
     * @throws \InvalidArgumentException When loading of XML file fails because of syntax errors
     *                                   or when the XML structure is not as expected by the scheme -
     *                                   see validate()
     */
    protected function loadFile($file)
    {
        return XmlUtils::loadFile($file);
    }

    /**
     * Parses the config elements (resource).
     *
     * @param \DOMElement $node Element to parse that contains the configs
     * @param string      $path Full path of the XML file being processed
     *
     * @return array An array with the resource items
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseConfigs(\DOMElement $node, $path)
    {
        $resources = [];

        /** @var \DOMElement $n */
        foreach ($node->getElementsByTagName('*') as $n) {
            if ($node !== $n->parentNode) {
                continue;
            }

            switch ($n->localName) {
                case 'resource':
                    $resources[] = [
                        'resource' => $n->nodeValue,
                        'type'     => $n->getAttribute('type'),
                    ];
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "default", "requirement" or "option".', $n->localName, $path));
            }
        }

        return $resources;
    }

    /**
     * Parses a node from a loaded XML file.
     *
     * @param array       $collection Collection to associate with the node
     * @param \DOMElement $node       Element to parse
     * @param string      $path       Full path of the XML file being processed
     * @param string      $file       Loaded file name
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseNode(array &$collection, \DOMElement $node, $path, $file)
    {
        switch ($node->localName) {
            case 'module':
                $this->parseMetadata($collection, $node, $path);
                break;
            case 'import':
                $this->parseImport($collection, $node, $path, $file);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "module" or "import".', $node->localName, $path));
        }
    }


    /**
     * Parses a metadata entry and adds it to the collection.
     *
     * @param array       $collection A collection of metadata
     * @param \DOMElement $node       Element to parse that represents a Route
     * @param string      $path       Full path of the XML file being processed
     */
    protected function parseMetadata(array &$collection, \DOMElement $node, $path)
    {
        if ('' === ($id = $node->getAttribute('id')) || !$node->hasAttribute('type')) {
            throw new \InvalidArgumentException(sprintf('The &lt;route&gt; element in file "%s" must have an "id" and a "type" attribute.', $path));
        }

        $metadata = [
            'name'    => $node->getAttribute('name'),
            'type'    => $node->getAttribute('type'),
            'routing' => $this->parseConfigs($node, $path),
        ];

        $collection[$id] = $metadata;
    }

    /**
     * Parses an import entry and adds the routes in the resource to the collection.
     *
     * @param array       $collection A collection of metadata
     * @param \DOMElement $node       Element to parse that represents a Route
     * @param string      $path       Full path of the XML file being processed
     * @param string      $file       Loaded file name
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseImport(array &$collection, \DOMElement $node, $path, $file)
    {
        if ('' === $resource = $node->getAttribute('resource')) {
            throw new \InvalidArgumentException(sprintf('The &lt;import&gt; element in file "%s" must have a "resource" attribute.', $path));
        }

        $type = $node->getAttribute('type');

        $this->setCurrentDir(dirname($path));

        $collection = array_merge($collection, $this->import($resource, $type, false, $file));
    }
}
