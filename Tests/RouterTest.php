<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Tests;

use Harmony\Component\ModularRouting\Router;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    private $router   = null;

    private $provider = null;

    protected function setUp()
    {
        $this->provider = $this->getMock('Harmony\Component\ModularRouting\Provider\ProviderInterface');

        $this->router = new Router($this->provider);
    }

    public function testSetOptionsWithSupportedOptions()
    {
        $this->router->setOptions(array(
            'cache_dir' => './cache',
            'debug' => true,
        ));

        $this->assertSame('./cache', $this->router->getOption('cache_dir'));
        $this->assertTrue($this->router->getOption('debug'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the following options: "option_foo", "option_bar"
     */
    public function testSetOptionsWithUnsupportedOptions()
    {
        $this->router->setOptions(array(
            'cache_dir' => './cache',
            'option_foo' => true,
            'option_bar' => 'baz',
        ));
    }

    public function testSetOptionWithSupportedOption()
    {
        $this->router->setOption('cache_dir', './cache');

        $this->assertSame('./cache', $this->router->getOption('cache_dir'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the "option_foo" option
     */
    public function testSetOptionWithUnsupportedOption()
    {
        $this->router->setOption('option_foo', true);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the "option_foo" option
     */
    public function testGetOptionWithUnsupportedOption()
    {
        $this->router->getOption('option_foo', true);
    }

    public function testMatchRequest()
    {
        $route  = new Route('/module/foo', ['test']);
        $routes = new RouteCollection;
        $routes->add('route_name', $route);

        $this->provider->expects($this->once())
            ->method('getRouteCollectionByModule')
            ->will($this->returnValue($routes));

        $this->router->matchRequest(Request::create('/module/foo'));
    }
}
