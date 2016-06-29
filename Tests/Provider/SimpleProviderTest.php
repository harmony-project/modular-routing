<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Tests\Provider;

use Harmony\Component\ModularRouting\Metadata\ModuleMetadata;
use Harmony\Component\ModularRouting\Provider\SimpleProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

class SimpleProviderTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    private $loader;

    private $manager;

    private $provider;

    public function setUp()
    {
        $this->manager = $this->getMock('Harmony\Component\ModularRouting\Manager\ModuleManagerInterface');

        $this->provider = new SimpleProvider($this->manager);
    }

    public function testGetModuleByParametersWithId()
    {
        $module = $this->getMock('Harmony\Component\ModularRouting\Model\Module');

        $this->manager->expects($this->once())
            ->method('findModuleBy')->with(['id' => 1])
            ->will($this->returnValue($module));

        $this->assertEquals($module, $this->provider->getModuleByParameters(['module' => 1]));
    }

    public function testGetModuleByParametersWithModule()
    {
        $module = $this->getMock('Harmony\Component\ModularRouting\Model\Module');

        $this->assertEquals($module, $this->provider->getModuleByParameters(['module' => $module]));
    }

    /**
     * @dataProvider getValidRequests
     */
    public function testGetModuleByRequest($request, $parameters)
    {
        $module = $this->getMock('Harmony\Component\ModularRouting\Model\Module');

        $this->manager->expects($this->once())
            ->method('findModuleBy')->with(['id' => 1])
            ->will($this->returnValue($module));

        $this->assertEquals($module, $this->provider->getModuleByRequest($request, $parameters));
    }

    public function getValidRequests()
    {
        return [
            [new Request, [
                '_modular_path' => '1',
            ]],
            [new Request, [
                '_modular_path' => '1/',
            ]],
            [new Request, [
                '_modular_path' => '1/bar',
            ]],
            [new Request, [
                '_modular_path' => '1/bar/',
            ]],
            [new Request, [
                '_modular_path' => '1/a_long/and_winded/request_path/12a34b56c/9',
            ]],
            [new Request, [
                '_modular_path'  => '1',
                'some_parameter' => 'bar',
            ]],
        ];
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @dataProvider getInvalidRequests
     */
    public function testGetModuleByRequestThrowsExceptionOnInvalidPath($request, $parameters)
    {
        $this->provider->getModuleByRequest($request, $parameters);
    }

    public function getInvalidRequests()
    {
        return [
            [new Request, [
                '_modular_path' => 'bar',
            ]],
            [new Request, [
                '_modular_path' => 'bar/',
            ]],
            [new Request, [
                '_modular_path' => 'bar/1',
            ]],
            [new Request, [
                '_modular_path' => 'bar/1/',
            ]],
            [new Request, [
                '_modular_path' => 'bar/12a34b56c/a_long/and_winded/request_path/9',
            ]],
            [new Request, [
                '_modular_path'  => 'bar',
                'some_parameter' => 'foo',
            ]],
        ];
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testGetModuleByRequestThrowsExceptionOnInvalidModule()
    {
        $this->manager->expects($this->once())
            ->method('findModuleBy')->with(['id' => 1])
            ->will($this->returnValue(null));

        $this->provider->getModuleByRequest(new Request, [
            '_modular_path' => '1',
        ]);
    }
}
