<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Tests\Metadata;

use Harmony\Component\ModularRouting\Metadata\MetadataFactory;
use Harmony\Component\ModularRouting\Metadata\NoSuchMetadataException;
use Harmony\Component\ModularRouting\RouteCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;

class MetadataFactoryTest extends TestCase
{
    private $factory;

    private $metadataLoader;

    private $routingLoader;

    protected function setUp()
    {
        $this->metadataLoader = $this->createMock(LoaderInterface::class);
        $this->routingLoader  = $this->createMock(LoaderInterface::class);
        $this->factory        = new MetadataFactory($this->metadataLoader, $this->routingLoader, 'routing.yml', 'ResourceType');
    }

    public function testConfiguredMetadata()
    {
        $this->metadataLoader->expects($this->once())
            ->method('load')->with('routing.yml', 'ResourceType')
            ->will($this->returnValue([
                [
                    'name' => 'Foo',
                    'type' => 'foo',
                    'routing' => [],
                ],
            ]));

        $this->assertTrue($this->factory->hasMetadataFor('foo'));

        $r = new \ReflectionClass('Harmony\Component\ModularRouting\Metadata\ModuleMetadata');
        $metadata = $r->newInstanceArgs(['Foo', 'foo', new RouteCollection]);

        $this->assertEquals($metadata, $this->factory->getMetadataFor('foo'));
    }

    /**
     * @expectedException NoSuchMetadataException
     */
    public function testUnconfiguredMetadata()
    {
        $this->metadataLoader->expects($this->once())
            ->method('load')->with('routing.yml', 'ResourceType')
            ->will($this->returnValue([]));

        $this->assertFalse($this->factory->hasMetadataFor('bar'));

        $this->factory->getMetadataFor('bar'); // exception
    }
}
