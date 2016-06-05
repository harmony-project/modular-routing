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
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;

/**
 * ProviderInterface
 *
 * Returns RouteCollection objects for Module instances.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
interface ProviderInterface
{
    /**
     * Returns the route collection associated with a module
     *
     * @param ModuleInterface $module
     *
     * @return RouteCollection
     */
    public function getRouteCollectionByModule(ModuleInterface $module);

    /**
     * Returns the Module instance by a set of parameters
     *
     * @param array $parameters Parameters to match
     *
     * @return ModuleInterface
     * @throws InvalidArgumentException  If one of the parameters has an invalid value
     * @throws ResourceNotFoundException If no module was matched to the parameters
     */
    public function getModuleByParameters(array $parameters);

    /**
     * Returns the Module instance associated with a request
     *
     * If the request does not have a module attribute, this method can require the following
     * parameters to be set to match the request to a Module:
     *
     *   * _modular_segment: Segment of path to use to match the request against
     *                       a module.
     *
     * @param Request $request    The request to match
     * @param array   $parameters Parameters returned by an UrlMatcher
     *
     * @return ModuleInterface
     * @throws InvalidArgumentException  If one of the parameters has an invalid value
     * @throws ResourceNotFoundException If no module was matched to the request
     */
    public function getModuleByRequest(Request $request, array $parameters = []);

    /**
     * Returns the segment to identify the module in a request path
     *
     * @param ModuleInterface $module
     *
     * @return string
     */
    public function getModularSegment(ModuleInterface $module);
}
