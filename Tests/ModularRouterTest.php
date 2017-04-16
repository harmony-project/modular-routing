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

use Harmony\Component\ModularRouting\Metadata\ModuleMetadata;
use Harmony\Component\ModularRouting\ModularRouter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ModularRouterTest extends TestCase
{
    private $factory;
    
    private $router;

    private $provider = null;

    protected function setUp()
    {
        $this->factory = $this
            ->getMockBuilder('Harmony\Component\ModularRouting\Metadata\MetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->provider = $this->createMock('Harmony\Component\ModularRouting\Provider\ProviderInterface');

        $this->router = new ModularRouter($this->provider, $this->factory);
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
     * @expectedException \InvalidArgumentException
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the "option_foo" option
     */
    public function testSetOptionWithUnsupportedOption()
    {
        $this->router->setOption('option_foo', true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Router does not support the "option_foo" option
     */
    public function testGetOptionWithUnsupportedOption()
    {
        $this->router->getOption('option_foo', true);
    }

    public function testGenerateWithId()
    {
        $module = $this->createMock('Harmony\Component\ModularRouting\Model\Module');

        $routes = new RouteCollection;
        $routes->add('bar', new Route('/module/{module}'));

        $metadata = new ModuleMetadata('Foo', 'foo', $routes);

        $this->provider->expects($this->once())
            ->method('loadModuleByParameters')
            ->will($this->returnValue($module));

        $this->factory->expects($this->once())
            ->method('hasMetadataFor')
            ->will($this->returnValue(true));

        $this->factory->expects($this->once())
            ->method('getMetadataFor')
            ->will($this->returnValue($metadata));

        $this->provider->expects($this->once())
            ->method('addModularPrefix');

        $this->assertEquals('/module/1', $this->router->generate('bar', ['module' => 1]));
    }

    public function testGenerateWithModule()
    {
        $module = $this->createMock('Harmony\Component\ModularRouting\Model\Module');

        $routes = new RouteCollection;
        $routes->add('bar', new Route('/module/{module}'));

        $metadata = new ModuleMetadata('Foo', 'foo', $routes);

        $module->expects($this->once())
            ->method('getModularIdentity')
            ->will($this->returnValue('1'));

        $this->factory->expects($this->once())
            ->method('hasMetadataFor')
            ->will($this->returnValue(true));

        $this->factory->expects($this->once())
            ->method('getMetadataFor')
            ->will($this->returnValue($metadata));

        $this->provider->expects($this->once())
            ->method('addModularPrefix');

        $this->assertEquals('/module/1', $this->router->generate('bar', ['module' => $module]));
    }

    public function testMatchRequest()
    {
        $module = $this->createMock('Harmony\Component\ModularRouting\Model\Module');

        $routes = new RouteCollection;
        $routes->add('bar', new Route('/module/{module}'));

        $metadata = new ModuleMetadata('Foo', 'foo', $routes);

        $this->provider->expects($this->once())
            ->method('loadModuleByRequest')
            ->will($this->returnValue($module));

        $this->factory->expects($this->once())
            ->method('hasMetadataFor')
            ->will($this->returnValue(true));

        $this->factory->expects($this->once())
            ->method('getMetadataFor')
            ->will($this->returnValue($metadata));

        $this->provider->expects($this->once())
            ->method('addModularPrefix');

        $this->assertEquals([
            'module' => 'foo',
            '_route' => 'bar',
        ], $this->router->matchRequest(Request::create('/module/foo')));
    }
}
