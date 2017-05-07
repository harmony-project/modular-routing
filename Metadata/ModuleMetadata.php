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

use Symfony\Component\Routing\RouteCollection;

/**
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class ModuleMetadata
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var RouteCollection
     */
    protected $routes;

    /**
     * @param string          $name
     * @param string          $type
     * @param RouteCollection $routes
     */
    public function __construct($name, $type, RouteCollection $routes)
    {
        $this->name   = $name;
        $this->type   = $type;
        $this->routes = $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
