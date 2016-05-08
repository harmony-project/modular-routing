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

/**
 * MetadataFactory
 *
 * Returns {@link ModuleMetadataInterface} instances by module type.
 *
 * TODO caching
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class MetadataFactory implements MetadataFactoryInterface
{
    /**
     * Collection of metadata instances
     *
     * @var array|bool Is false before loading the collection
     */
    private $collection = false;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var mixed
     */
    private $resource;

    /**
     * @var mixed
     */
    private $resourceType;

    /**
     * MetadataFactory constructor
     *
     * @param LoaderInterface $loader       A loader instance
     * @param mixed           $resource     The main resource to load
     * @param mixed           $resourceType Type hint for the main resource (optional)
     */
    public function __construct(LoaderInterface $loader, $resource, $resourceType = null)
    {
        $this->loader       = $loader;
        $this->resource     = $resource;
        $this->resourceType = $resourceType;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {
        $collection = $this->getMetadataCollection();

        foreach ($collection as $metadata) {
            if ($value == $metadata->getType()) {
                return $metadata;
            }
        }

        throw new NoSuchMetadataException(sprintf('No metadata found for module type "%s".', $value));
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($value)
    {
        $collection = $this->getMetadataCollection();

        foreach ($collection as $metadata) {
            if ($value == $metadata->getType()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the collection of metadata instance
     *
     * @return array|bool
     */
    public function getMetadataCollection()
    {
        if (false !== $this->collection) {
            return $this->collection;
        }

        $this->collection = [];

        $config = $this->loader->load($this->resource, $this->resourceType);

        if (null === $config) {
            return $this->collection;
        }

        foreach ($config as $name => $options) {
            $metadata = new ModuleMetadata($options['name'], $options['type'], $options['routing']);

            $this->collection[$name] = $metadata;
        }

        return $this->collection;
    }
}
