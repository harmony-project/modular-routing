<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting;

use Harmony\Component\ModularRouting\Metadata\MetadataFactoryInterface;
use Harmony\Component\ModularRouting\Provider\ProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing as SymfonyRouting;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\Dumper\GeneratorDumperInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\Dumper\MatcherDumperInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * This router uses custom Provider objects to retrieve RouteCollection instances.
 *
 * Inspired by Symfony Router and Symfony CMF DynamicRouter.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class ModularRouter implements RouterInterface, RequestMatcherInterface, ChainedRouterInterface
{
    /**
     * @var RouteCollection[]
     */
    private $collections = [];

    /**
     * @var ConfigCacheFactoryInterface|null
     */
    private $configCacheFactory;

    /**
     * @var RequestContext|null
     */
    private $context;

    /**
     * @var array
     */
    private $generators = [];

    /**
     * @var UrlMatcher
     */
    private $initialMatcher = null;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var array
     */
    private $matchers = [];

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var array
     */
    private $options = [];

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var array
     */
    private $routePrefix = [];

    /**
     * @param ProviderInterface        $provider
     * @param MetadataFactoryInterface $metadataFactory
     * @param array                    $options
     * @param RequestContext|null      $context
     * @param LoggerInterface|null     $logger
     */
    public function __construct(
        ProviderInterface $provider,
        MetadataFactoryInterface $metadataFactory,
        array $options = [],
        RequestContext $context = null,
        LoggerInterface $logger = null
    )
    {
        $this->provider        = $provider;
        $this->metadataFactory = $metadataFactory;
        $this->context         = $context ?: new RequestContext;

        $this->routePrefix = [
            'path'         => '',
            'defaults'     => [],
            'requirements' => [],
        ];

        $this->setOptions($options);
    }

    /**
     * Sets options.
     *
     * Available options:
     *
     *   * cache_dir:              The cache directory (or null to disable caching)
     *   * debug:                  Whether to enable debugging or not (false by default)
     *   * generator_class:        The name of a UrlGeneratorInterface implementation
     *   * generator_base_class:   The base class for the dumped generator class
     *   * generator_dumper_class: The name of a GeneratorDumperInterface implementation
     *   * matcher_class:          The name of a UrlMatcherInterface implementation
     *   * matcher_base_class:     The base class for the dumped matcher class
     *   * matcher_dumper_class:   The class name for the dumped matcher class
     *   * strict_requirements:    Configure strict requirement checking for generators
     *                             implementing ConfigurableRequirementsInterface (default is true)
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException When a unsupported option is provided
     */
    public function setOptions(array $options)
    {
        $this->options = [
            'cache_dir'           => null,
            'debug'               => false,
            'strict_requirements' => true,

            'generator_class'        => SymfonyRouting\Generator\UrlGenerator::class,
            'generator_base_class'   => SymfonyRouting\Generator\UrlGenerator::class,
            'generator_dumper_class' => SymfonyRouting\Generator\Dumper\PhpGeneratorDumper::class,

            'matcher_class'        => SymfonyRouting\Matcher\UrlMatcher::class,
            'matcher_base_class'   => SymfonyRouting\Matcher\UrlMatcher::class,
            'matcher_dumper_class' => SymfonyRouting\Matcher\Dumper\PhpMatcherDumper::class,
        ];

        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid = [];
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                $invalid[] = $key;
            }
        }

        if ($invalid) {
            throw new \InvalidArgumentException(sprintf('ModularRouter does not support the following options: "%s".', implode('", "', $invalid)));
        }
    }

    /**
     * Sets an option.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws \InvalidArgumentException When a unsupported option is provided
     */
    public function setOption($key, $value)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('ModularRouter does not support the "%s" option.', $key));
        }

        $this->options[$key] = $value;
    }

    /**
     * Gets an option value.
     *
     * @param string $key
     *
     * @return mixed
     * @throws \InvalidArgumentException When a unsupported option is provided
     */
    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new \InvalidArgumentException(sprintf('ModularRouter does not support the "%s" option.', $key));
        }

        return $this->options[$key];
    }

    /**
     * Sets the ConfigCache factory to use.
     *
     * @param ConfigCacheFactoryInterface $configCacheFactory
     */
    public function setConfigCacheFactory(ConfigCacheFactoryInterface $configCacheFactory)
    {
        $this->configCacheFactory = $configCacheFactory;
    }

    /**
     * Provides the ConfigCache factory implementation, falling back to a
     * default implementation if necessary.
     *
     * @return ConfigCacheFactoryInterface $configCacheFactory
     */
    private function getConfigCacheFactory()
    {
        if (null === $this->configCacheFactory) {
            $this->configCacheFactory = new ConfigCacheFactory($this->options['debug']);
        }

        return $this->configCacheFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;

        foreach ($this->matchers as $matcher) {
            $matcher->setContext($context);
        }

        foreach ($this->generators as $generator) {
            $generator->setContext($context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        // Since this Router has more than 1 route collection we return an empty RouteCollection
        return new RouteCollection;
    }

    /**
     * Returns the route collection for a module type.
     *
     * @param string $type
     *
     * @return RouteCollection
     */
    public function getRouteCollectionForType($type)
    {
        if (isset($this->collections[$type])) {
            return $this->collections[$type];
        }

        if (!$this->metadataFactory->hasMetadataFor($type)) {
            throw new ResourceNotFoundException(sprintf('No metadata found for module type "%s".', $type));
        }

        $metadata = $this->metadataFactory->getMetadataFor($type);
        $routes   = $metadata->getRoutes();

        // Add modular prefix to the collection
        $this->provider->addModularPrefix($routes);

        // Add route prefix to the collection
        $routes->addPrefix(
            $this->routePrefix['path'],
            $this->routePrefix['defaults'],
            $this->routePrefix['requirements']
        );

        return $this->collections[$type] = $routes;
    }

    /**
     * Sets the prefix for the path of all modular routes.
     *
     * @param string $prefix       An optional prefix to add before each pattern of the route collection
     * @param array  $defaults     The default values of parameters in the prefix
     * @param array  $requirements The requirements of parameters in the prefix
     */
    public function setRoutePrefix($prefix, array $defaults = [], array $requirements = [])
    {
        $this->routePrefix = [
            'path'         => $prefix,
            'defaults'     => $defaults,
            'requirements' => $requirements,
        ];
    }

    /**
     * Returns a generator for a module.
     *
     * @param ModuleInterface $module
     *
     * @return UrlGeneratorInterface
     */
    public function getGeneratorForModule(ModuleInterface $module)
    {
        $type       = $module->getModularType();
        $collection = $this->getRouteCollectionForType($type);

        if (array_key_exists($type, $this->generators)) {
            return $this->generators[$type];
        }

        if (null === $this->options['cache_dir']) {
            return $this->generators[$type] = new $this->options['generator_class']($collection, $this->context, $this->logger);
        }

        $cacheClass = sprintf('%s_UrlGenerator', $type);

        $cache = $this->getConfigCacheFactory()->cache($this->options['cache_dir'] . '/' . $cacheClass .'.php',
            function (ConfigCacheInterface $cache) use ($cacheClass, $collection) {
                /** @var GeneratorDumperInterface $dumper */
                $dumper = new $this->options['generator_dumper_class']($collection);

                $options = [
                    'class'      => $cacheClass,
                    'base_class' => $this->options['generator_base_class'],
                ];

                $cache->write($dumper->dump($options), $collection->getResources());
            }
        );

        require_once $cache->getPath();

        return $this->generators[$type] = new $cacheClass($this->context, $this->logger);
    }

    /**
     * Returns a matcher for a module.
     *
     * @param ModuleInterface $module
     *
     * @return UrlMatcherInterface|RequestMatcherInterface
     */
    public function getMatcherForModule(ModuleInterface $module)
    {
        $type       = $module->getModularType();
        $collection = $this->getRouteCollectionForType($type);

        if (array_key_exists($type, $this->matchers)) {
            return $this->matchers[$type];
        }

        if (null === $this->options['cache_dir']) {
            return $this->matchers[$type] = new $this->options['matcher_class']($collection, $this->context);
        }

        $cacheClass = sprintf('%s_UrlMatcher', $type);

        $cache = $this->getConfigCacheFactory()->cache($this->options['cache_dir'] . '/' . $cacheClass .'.php',
            function (ConfigCacheInterface $cache) use ($cacheClass, $collection) {
                /** @var MatcherDumperInterface $dumper */
                $dumper = new $this->options['matcher_dumper_class']($collection);

                $options = [
                    'class'      => $cacheClass,
                    'base_class' => $this->options['matcher_base_class'],
                ];

                $cache->write($dumper->dump($options), $collection->getResources());
            }
        );

        require_once $cache->getPath();

        return $this->matchers[$type] = new $cacheClass($this->context);
    }

    /**
     * Returns a module by matching the request.
     *
     * @param Request $request The request to match
     *
     * @return ModuleInterface
     * @throws ResourceNotFoundException If no matching resource or module could be found
     * @throws MethodNotAllowedException If a matching resource was found but the request method is not allowed
     */
    public function getModuleByRequest(Request $request)
    {
        $parameters = $this->getInitialMatcher()->matchRequest($request);

        // Since a matcher throws an exception on failure, this will only be reached
        // if the match was successful.

        return $this->provider->loadModuleByRequest($request, $parameters);
    }

    /**
     * Returns a matcher that matches a Request to the route prefix.
     *
     * @return UrlMatcher
     */
    public function getInitialMatcher()
    {
        if (null !== $this->initialMatcher) {
            return $this->initialMatcher;
        }

        $route = sprintf('%s/{_modular_path}', $this->routePrefix['path']);

        $collection = new RouteCollection;
        $collection->add('modular', new Route(
            $route,
            $this->routePrefix['defaults'],
            array_merge(
                $this->routePrefix['requirements'],
                ['_modular_path' => '.+']
            )
        ));

        return $this->initialMatcher = new UrlMatcher($collection, $this->context);
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        if (isset($parameters['module']) && $parameters['module'] instanceof ModuleInterface) {
            $module = $parameters['module'];

            $parameters['module'] = $module->getModularIdentity();
        }
        else {
            try {
                $module = $this->provider->loadModuleByParameters($parameters);
            }
            catch (\Exception $e) {
                throw new RouteNotFoundException($e->getMessage());
            }
        }

        $generator = $this->getGeneratorForModule($module);

        return $generator->generate($name, $parameters, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        $request = Request::create($pathinfo);

        return $this->matchRequest($request);
    }

    /**
     * {@inheritdoc}
     */
    public function matchRequest(Request $request)
    {
        $module = $this->getModuleByRequest($request);

        $matcher = $this->getMatcherForModule($module);
        if ($matcher instanceof RequestMatcherInterface) {
            $defaults = $matcher->matchRequest($request);
        } else {
            $defaults = $matcher->match($request->getPathInfo());
        }

        return $defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        return is_string($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteDebugMessage($name, array $parameters = [])
    {
        if (is_scalar($name)) {
            return $name;
        }

        if (is_array($name)) {
            return serialize($name);
        }

        return get_class($name);
    }
}
