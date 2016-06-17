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

use InvalidArgumentException;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * YamlFileLoader
 *
 * Loads modular routing metadata files formatted in YAML.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class YamlFileLoader extends FileLoader
{
    /**
     * @var array
     */
    private static $availableKeys = [
        'name', 'type', 'routing', 'resource'
    ];

    /**
     * @var YamlParser
     */
    protected $yamlParser;

    /**
     * Loads a YAML file.
     *
     * @param string      $file The file path
     * @param string|null $type The resource type
     *
     * @return array A collection of metadata
     * @throws InvalidArgumentException When metadata can't be parsed because YAML is invalid.
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        if (!stream_is_local($path)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser;
        }

        try {
            $parsedConfig = $this->yamlParser->parse(file_get_contents($path));
        } catch (ParseException $e) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $path), 0, $e);
        }

        $collection = [];

        // empty file
        if (null === $parsedConfig) {
            return $collection;
        }

        // not an array
        if (!is_array($parsedConfig)) {
            throw new InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        foreach ($parsedConfig as $name => $config) {
            $this->validate($config, $name, $path);

            if (isset($config['resource'])) {
                $this->parseImport($collection, $config, $path, $file);
            } else {
                $this->parseMetadata($collection, $name, $config);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), ['yml', 'yaml'], true) && (!$type || 'yaml' === $type);
    }

    /**
     * Parses a metadata entry and adds it to the collection.
     *
     * @param array  $collection A collection of metadata
     * @param string $configName Name of the metadata
     * @param array  $config     The metadata options
     */
    protected function parseMetadata(array &$collection, $configName, array $config)
    {
        $name     = isset($config['name']) ? $config['name'] : null;
        $type     = isset($config['type']) ? $config['type'] : null;
        $routing  = isset($config['routing']) ? $config['routing'] : [];

        $metadata = [
            'name'     => $name,
            'type'     => $type,
            'routing'  => $routing,
        ];

        $collection[$configName] = $metadata;
    }

    /**
     * Parses an import entry and adds the routes in the resource to the collection.
     *
     * @param array  $collection A collection of metadata
     * @param array  $config     The metadata options
     * @param string $path       Full path of the YAML file being processed
     * @param string $file       Loaded file name
     *
     * @throws \Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException
     * @throws \Symfony\Component\Config\Exception\FileLoaderLoadException
     */
    protected function parseImport(array &$collection, array $config, $path, $file)
    {
        $type = isset($config['type']) ? $config['type'] : null;

        $this->setCurrentDir(dirname($path));

        $collection = array_merge($collection, $this->import($config['resource'], $type, false, $file));
    }

    /**
     * Validates the metadata configuration.
     *
     * @param array  $config A resource config
     * @param string $name   The config key
     * @param string $path   The loaded file path
     *
     * @throws InvalidArgumentException If one of the provided config keys is not supported,
     *                                  something is missing or the combination is invalid.
     */
    protected function validate($config, $name, $path)
    {
        if (!is_array($config)) {
            throw new InvalidArgumentException(sprintf('The definition of "%s" in "%s" must be a YAML array.', $name, $path));
        }
        if ($extraKeys = array_diff(array_keys($config), self::$availableKeys)) {
            throw new InvalidArgumentException(sprintf(
                'The routing file "%s" contains unsupported keys for "%s": "%s". Expected one of: "%s".',
                $path, $name, implode('", "', $extraKeys), implode('", "', self::$availableKeys)
            ));
        }

        // check here if the combination of config options is invalid
    }
}
