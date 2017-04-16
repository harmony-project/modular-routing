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
use Harmony\Component\ModularRouting\Provider\SegmentProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

class SegmentProviderTest extends TestCase
{
    private $manager;

    private $provider;

    public function setUp()
    {
        $this->manager = $this->createMock('Harmony\Component\ModularRouting\Manager\ModuleManagerInterface');

        $this->provider = new SegmentProvider($this->manager);
    }

    public function testLoadModuleByParametersWithId()
    {
        $module = $this->createMock('Harmony\Component\ModularRouting\Model\Module');

        $this->manager->expects($this->once())
            ->method('findModuleByIdentity')->with(1)
            ->will($this->returnValue($module));

        $this->assertEquals($module, $this->provider->loadModuleByParameters(['module' => 1]));
    }

    public function testLoadModuleByParametersWithModule()
    {
        $module = $this->createMock('Harmony\Component\ModularRouting\Model\Module');

        $this->assertEquals($module, $this->provider->loadModuleByParameters(['module' => $module]));
    }

    /**
     * @dataProvider getValidRequests
     */
    public function testLoadModuleByRequest($request, $parameters)
    {
        $module = $this->createMock('Harmony\Component\ModularRouting\Model\Module');

        $this->manager->expects($this->once())
            ->method('findModuleByIdentity')->with(1)
            ->will($this->returnValue($module));

        $this->assertEquals($module, $this->provider->loadModuleByRequest($request, $parameters));
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
    public function testLoadModuleByRequestThrowsExceptionOnInvalidPath($request, $parameters)
    {
        $this->provider->loadModuleByRequest($request, $parameters);
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
    public function testLoadModuleByRequestThrowsExceptionOnInvalidModule()
    {
        $this->manager->expects($this->once())
            ->method('findModuleByIdentity')->with(1)
            ->will($this->returnValue(null));

        $this->provider->loadModuleByRequest(new Request, [
            '_modular_path' => '1',
        ]);
    }
}
