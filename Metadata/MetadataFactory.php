<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Metadata;

use Symfony\Component\Config\Loader\LoaderInterface;
use Harmony\Component\ModularRouting\RouteCollection;

/**
 * Returns {@link ModuleMetadataInterface} instances by module type.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * Collection of metadata instances.
     *
     * @var array
     */
    private $collection = [];

    /**
     * Collection of definitions.
     *
     * @var array|bool Is false before loading definitions
     */
    private $definitions;

    /**
     * @var LoaderInterface
     */
    private $metadataLoader;

    /**
     * @var LoaderInterface
     */
    private $routingLoader;

    /**
     * @var mixed
     */
    private $resource;

    /**
     * @var string
     */
    private $resourceType;

    /**
     * MetadataFactory constructor
     *
     * @param LoaderInterface $metadataLoader A metadata loader instance
     * @param LoaderInterface $routingLoader  A routing loader instance
     * @param mixed           $resource       The main resource to load
     * @param string          $resourceType   Type hint for the main resource (optional)
     */
    public function __construct(LoaderInterface $metadataLoader, LoaderInterface $routingLoader, $resource, $resourceType = null)
    {
        $this->metadataLoader = $metadataLoader;
        $this->routingLoader  = $routingLoader;
        $this->resource       = $resource;
        $this->resourceType   = $resourceType;

        $this->definitions = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {
        if (isset($this->collection[$value])) {
            return $this->collection[$value];
        }

        return $this->loadMetadataFor($value);
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($value)
    {
        if (isset($this->collection[$value])) {
            return true;
        }

        $definitions = $this->getDefinitions();

        foreach ($definitions as $name => $options) {
            if ($value == $options['type']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Loads metadata for a given type
     *
     * @param string $value
     *
     * @return ModuleMetadata
     * @throws NoSuchMetadataException If no metadata exists for the given value
     */
    protected function loadMetadataFor($value)
    {
        $definitions = $this->getDefinitions();

        foreach ($definitions as $name => $options) {
            if ($value == $options['type']) {
                $definition = $options;

                break;
            }
        }

        if (!isset($definition)) {
            throw new NoSuchMetadataException(sprintf('No metadata found for module type "%s".', $value));
        }

        // Build route collection
        $collection = new RouteCollection;

        foreach ($definition['routing'] as $resource) {
            $resourceType = isset($resource['type']) ? $resource['type'] : null;

            $subCollection = $this->routingLoader->load($resource['resource'], $resourceType);

            $collection->addCollection($subCollection);
        }

        // Build metadata
        $metadata = new ModuleMetadata($definition['name'], $definition['type'], $collection);

        return $this->collection[$definition['type']] = $metadata;
    }

    /**
     * Returns the metadata definitions
     *
     * @return array|bool
     */
    public function getDefinitions()
    {
        if (false !== $this->definitions) {
            return $this->definitions;
        }

        $config = $this->metadataLoader->load($this->resource, $this->resourceType);

        if (null === $config) {
            return [];
        }

        return $this->definitions = $config;
    }
}
