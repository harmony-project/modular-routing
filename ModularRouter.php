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

use Harmony\Component\ModularRouting\Model\ModuleInterface;
use Harmony\Component\ModularRouting\Provider\ProviderInterface;
use InvalidArgumentException;
use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * ModularRouter
 *
 * This router uses custom Provider objects to retrieve RouteCollection instances.
 *
 * Inspired by Symfony Router and Symfony CMF DynamicRouter
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class ModularRouter implements RouterInterface, RequestMatcherInterface, ChainedRouterInterface
{
    /**
     * @var RequestContext
     */
    private $context;

    /**
     * An array of options
     *
     * @var array
     */
    private $options = [];

    /**
     * Provider object for retrieving route collections
     *
     * @var ProviderInterface
     */
    private $provider;

    /**
     * Router constructor
     *
     * @param ProviderInterface   $provider
     * @param array               $options
     * @param RequestContext|null $context
     */
    public function __construct(ProviderInterface $provider, array $options = [], RequestContext $context = null)
    {
        $this->provider = $provider;
        $this->context  = $context ?: new RequestContext;

        $this->options = array(
            'cache_dir'           => null,
            'debug'               => false,
            'route_prefix'        => '/module',
            'strict_requirements' => true,

            'generator_class'        => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'generator_base_class'   => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'generator_dumper_class' => 'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper',

            'matcher_class'        => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
            'matcher_base_class'   => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
            'matcher_dumper_class' => 'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper',
        );

        $this->setOptions($options);
    }

    /**
     * Sets options
     *
     * Available options:
     *
     *   * cache_dir:      The cache directory (or null to disable caching)
     *   * debug:          Whether to enable debugging or not (false by default)
     *   * route_prefix: The prefix
     *
     * @param array $options An array of options
     *
     * @throws InvalidArgumentException When unsupported option is provided
     */
    public function setOptions(array $options)
    {
        // check option names and live merge, if errors are encountered Exception will be thrown
        $invalid = array();
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->options)) {
                $this->options[$key] = $value;
            } else {
                $invalid[] = $key;
            }
        }

        if ($invalid) {
            throw new InvalidArgumentException(sprintf('The Router does not support the following options: "%s".', implode('", "', $invalid)));
        }
    }

    /**
     * Sets an option
     *
     * @param string $key   The key
     * @param mixed  $value The value
     *
     * @throws InvalidArgumentException
     */
    public function setOption($key, $value)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        $this->options[$key] = $value;
    }

    /**
     * Gets an option value
     *
     * @param string $key The key
     *
     * @return mixed The value
     * @throws InvalidArgumentException
     */
    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            throw new InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
        }

        return $this->options[$key];
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
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Returns a generator for a Module
     *
     * @param ModuleInterface $module
     *
     * @return UrlGeneratorInterface
     */
    public function getGeneratorForModule(ModuleInterface $module)
    {
        $collection = $this->provider->getRouteCollectionByModule($module);

        // TODO caching
        $generator = new $this->options['generator_class']($collection, $this->context);

        return $generator;
    }

    /**
     * Returns a matcher for a Module
     *
     * @param ModuleInterface $module
     *
     * @return UrlMatcherInterface|RequestMatcherInterface
     */
    public function getMatcherForModule(ModuleInterface $module)
    {
        $collection = $this->provider->getRouteCollectionByModule($module);

        // TODO caching
        $matcher = new $this->options['matcher_class']($collection, $this->context);

        return $matcher;
    }

    /**
     * Returns a module by matching the request
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

        return $this->provider->getModuleByRequest($request, $parameters);
    }

    /**
     * Returns a matcher that matches a Request to the route prefix
     *
     * @return UrlMatcher
     */
    public function getInitialMatcher()
    {
        $route = sprintf('%s/{_modular_segment}', $this->options['route_prefix']);

        $collection = new RouteCollection;
        $collection->add('modular', new Route(
            $route,
            [],
            ['_modular_segment' => '.+']
        ));

        return new UrlMatcher($collection, $this->context);
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        if ($parameters['module'] instanceof ModuleInterface) {
            $module = $parameters['module'];
            
            $parameters['module'] = $this->provider->getModularSegment($module);
        }
        else {
            $module = $this->provider->getModuleByParameters($parameters); // todo add exceptions to method doc block
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
        if ($matcher instanceof UrlMatcherInterface) {
            $defaults = $matcher->match($request->getPathInfo());
        } else {
            $defaults = $matcher->matchRequest($request);
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
    public function getRouteDebugMessage($name, array $parameters = array())
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
