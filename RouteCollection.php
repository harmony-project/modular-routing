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

use Symfony\Component\Routing\RouteCollection as BaseCollection;

/**
 * RouteCollection adapted for use by modules.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class RouteCollection extends BaseCollection
{
    public function addPrefix($prefix, array $defaults = [], array $requirements = [])
    {
        $prefix = trim(trim($prefix), '/');

        if ('' === $prefix) {
            return;
        }

        foreach ($this->all() as $route) {
            if ($route->getPath() !== '/') {
                $route->setPath('/' . $prefix . $route->getPath());
            }
            else {
                // Prevent "directory" structure of the route prefix, see https://github.com/symfony/symfony/issues/12141
                $route->setPath('/' . $prefix);
            }

            $route->addDefaults($defaults);
            $route->addRequirements($requirements);
        }
    }
}
