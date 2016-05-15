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
use Symfony\Component\Routing\Exception\RouteNotFoundException;
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
     * @param ModuleInterface|int $module Module object or id
     *
     * @return RouteCollection
     * @throws RouteNotFoundException If the module doesn't exist
     */
    public function getRouteCollectionByModule($module);

    /**
     * Returns the Module instance associated with a request
     *
     * Required parameters:
     *
     *   * _modular_segment: Segment of path to use to match the request against
     *                       a module.
     *
     * @param Request $request    The request to match
     * @param array   $parameters Parameters returned by an UrlMatcher
     *
     * @return ModuleInterface
     * @throws RouteNotFoundException If the module doesn't exist
     */
    public function getModuleByRequest(Request $request, array $parameters = []);
}
