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
use Harmony\Component\ModularRouting\ModuleInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;

/**
 * Loads {@link ModuleInterface} instances based on a segment of the request path.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class SegmentProvider implements ProviderInterface
{
    /**
     * @var ModuleManagerInterface
     */
    private $manager;

    /**
     * @param ModuleManagerInterface $manager
     */
    public function __construct(ModuleManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function addModularPrefix(RouteCollection $routes)
    {
        $routes->addPrefix(
            '/{module}',
            [],
            ['module' => '[^/]+'] // actually redundant
        );
    }

    /**
     * {@inheritdoc}
     * 
     * The "module" parameter is required to map the correct module, this
     * can either be a {@link ModuleInterface} object or its identity.
     */
    public function loadModuleByParameters(array $parameters)
    {
        if (!isset($parameters['module'])) {
            throw new \InvalidArgumentException('The routing provider expected parameter "module" but could not find it.');
        }

        if ($parameters['module'] instanceof ModuleInterface) {
            return $parameters['module'];
        }

        $identity = $parameters['module'];

        // Get the related module
        $module = $this->manager->findModuleByIdentity($identity);

        if (!$module instanceof ModuleInterface) {
            throw new ResourceNotFoundException(sprintf('Module with identity "%s" does not exist.', $identity));
        }

        return $module;
    }

    /**
     * {@inheritdoc}
     */
    public function loadModuleByRequest(Request $request, array $parameters = [])
    {
        if (null !== $module = $request->attributes->get('module')) {
            if ($module instanceof ModuleInterface) {
                return $module;
            }

            $identity = $module;
        }
        else {
            $identity = $this->matchRequest($request, $parameters);
        }

        // Get the related module
        $module = $this->manager->findModuleByIdentity($identity);

        if (!$module instanceof ModuleInterface) {
            throw new ResourceNotFoundException(sprintf('Module with identity "%s" does not exist.', $identity));
        }

        return $module;
    }

    /**
     * Filters the module identity from the request path.
     *
     * @param Request $request    The request to match
     * @param array   $parameters Parameters returned by an UrlMatcher
     *
     * @return string
     * @throws \InvalidArgumentException If the "_modular_path" parameter is not set
     */
    protected function matchRequest(Request $request, array $parameters = [])
    {
        if (!isset($parameters['_modular_path'])) {
            throw new \InvalidArgumentException('The routing provider expected parameter "_modular_path" but could not find it.');
        }

        // Grab the first segment from the remaining path
        $path = $parameters['_modular_path'];
        $position = strpos($path, '/');

        return $position ? substr($path, 0, $position) : $path;
    }
}
