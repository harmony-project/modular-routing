<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Provider;

use Harmony\Component\ModularRouting\Manager\ModuleManagerInterface;
use Harmony\Component\ModularRouting\Metadata\MetadataFactoryInterface;
use Harmony\Component\ModularRouting\Model\ModuleInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;

/**
 * SimpleProvider
 *
 * Returns RouteCollection objects for Module instances based on their id.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class SimpleProvider implements ProviderInterface
{
    /**
     * All loaded RouteCollection instances sorted by module type
     *
     * @var array
     */
    private $collections = [];

    /**
     * Routing loader
     *
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var ModuleManagerInterface
     */
    private $moduleManager;

    /**
     * @var string
     */
    private $routePrefix;

    /**
     * SimpleProvider constructor
     *
     * @param MetadataFactoryInterface $metadataFactory
     * @param LoaderInterface          $loader
     * @param ModuleManagerInterface   $moduleManager
     * @param string                   $routePrefix
     */
    public function __construct(MetadataFactoryInterface $metadataFactory, LoaderInterface $loader, ModuleManagerInterface $moduleManager, $routePrefix = '')
    {
        $this->metadataFactory = $metadataFactory;
        $this->loader          = $loader;
        $this->moduleManager   = $moduleManager;
        $this->routePrefix     = $routePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getModularSegment(ModuleInterface $module)
    {
        return $module->getId();
    }

    /**
     * Returns the Module instance by a set of parameters
     * 
     * The "module" parameter is required to map the Module object, this can be either the Module object or a Module id
     *
     * @param array $parameters Parameters to match
     *
     * @return ModuleInterface
     * @throws \InvalidArgumentException  If one of the parameters has an invalid value
     * @throws ResourceNotFoundException If no module was matched to the parameters
     */
    public function getModuleByParameters(array $parameters)
    {
        if (!isset($parameters['module'])) {
            throw new \InvalidArgumentException('The routing provider expects parameter "module" but could not be found.');
        }

        if ($parameters['module'] instanceof ModuleInterface) {
            return $parameters['module'];
        }

        $id = $parameters['module'];

        $module = $this->moduleManager->findModuleBy(['id' => $id]);

        if (!$module instanceof ModuleInterface) {
            throw new ResourceNotFoundException(sprintf('Module with id "%s" does not exist.', $id));
        }

        return $module;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleByRequest(Request $request, array $parameters = [])
    {
        if (null !== $module = $request->attributes->get('module')) {
            if ($module instanceof ModuleInterface) {
                return $module;
            }

            $module = $this->moduleManager->findModuleBy(['id' => $module]);

            if (!$module instanceof ModuleInterface) {
                throw new ResourceNotFoundException(sprintf('Module with path "%s" does not exist.', $module));
            }

            return $module;
        }

        $id = $this->matchRequest($request, $parameters);

        // Get the related module
        $module = $this->moduleManager->findModuleBy(['id' => $id]);

        if (null === $module) {
            throw new ResourceNotFoundException(sprintf('Module with id "%s" does not exist.', $id));
        }

        return $module;
    }

    /**
     * Returns a prepared route collection
     *
     * @param string $type Module type
     *
     * @return RouteCollection
     */
    public function getRouteCollection($type)
    {
        if (isset($this->collections[$type])) {
            return $this->collections[$type];
        }

        // Get related metadata
        $metadata   = $this->metadataFactory->getMetadataFor($type);
        $resources  = $metadata->getRouting();
        $collection = new RouteCollection;

        // Build route collection
        foreach ($resources as $resource) {
            $resourceType = isset($resource['type']) ? $resource['type'] : null;

            $subCollection = $this->loader->load($resource['resource'], $resourceType);

            $collection->addCollection($subCollection);
        }

        // Add routing prefix to the collection
        $route = sprintf('%s/{module}', $this->routePrefix);

        $collection->addPrefix(
            $route,
            [],
            ['module' => '\d+']
        );

        return $this->collections[$type] = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollectionByModule(ModuleInterface $module)
    {
        // Get route collection
        $collection = $this->getRouteCollection($module->getType());

        return $collection;
    }

    /**
     * Filters the Module id from a request path
     *
     * @param Request $request    The request to match
     * @param array   $parameters Parameters returned by an UrlMatcher
     *
     * @return string
     */
    protected function matchRequest(Request $request, array $parameters = [])
    {
        if (!isset($parameters['_modular_segment'])) {
            throw new \InvalidArgumentException('The parameter "_modular_segment" must be set.');
        }

        // Match the module in _modular_segment
        $segment = $parameters['_modular_segment'];
        $pos     = strpos($segment, '/');

        return $pos ? substr($segment, 0, $pos) : $segment;
    }
}
