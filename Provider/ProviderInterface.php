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

use Harmony\Component\ModularRouting\Model\ModuleInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;

/**
 * Returns RouteCollection objects for Module instances.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
interface ProviderInterface
{
    /**
     * Adds the modular routing prefix to a route collection.
     *
     * @param RouteCollection $routes
     */
    public function addModularPrefix(RouteCollection $routes);

    /**
     * Loads the Module instance by a set of parameters.
     *
     * @param array $parameters Parameters to match
     *
     * @return ModuleInterface
     * @throws \InvalidArgumentException If one of the parameters has an invalid value
     * @throws ResourceNotFoundException If no module was matched to the parameters
     */
    public function loadModuleByParameters(array $parameters);

    /**
     * Loads the Module instance associated with a request.
     *
     * If the request does not have a module attribute, this method can require the following
     * parameters to be set to match the request to a Module:
     *
     *   * _modular_path: Remaining path to use to match the request against
     *                    a module.
     *
     * @param Request $request    The request to match
     * @param array   $parameters Parameters returned by an UrlMatcher
     *
     * @return ModuleInterface
     * @throws \InvalidArgumentException If one of the parameters has an invalid value
     * @throws ResourceNotFoundException If no module was matched to the request
     */
    public function loadModuleByRequest(Request $request, array $parameters = []);
}
