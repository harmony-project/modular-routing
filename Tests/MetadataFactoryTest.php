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

use Harmony\Component\ModularRouting\Metadata\MetadataFactory;

class MetadataFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory = null;

    private $loader = null;

    protected function setUp()
    {
        $this->loader  = $this->getMock('Symfony\Component\Config\Loader\LoaderInterface');
        $this->factory = new MetadataFactory($this->loader, 'routing.yml', 'ResourceType');
    }

    public function testThatCollectionIsLoaded()
    {
        $collection = [];

        $this->loader->expects($this->once())
            ->method('load')->with('routing.yml', 'ResourceType')
            ->will($this->returnValue([]));

        $this->assertEquals($collection, $this->factory->getMetadataCollection());
    }

    public function testConfiguredMetadata()
    {
        $this->loader->expects($this->once())
            ->method('load')->with('routing.yml', 'ResourceType')
            ->will($this->returnValue([
                [
                    'name' => 'Test',
                    'type' => 'test',
                    'routing' => [],
                ],
            ]));

        $this->assertTrue($this->factory->hasMetadataFor('test'));

        $r = new \ReflectionClass('Harmony\\Component\\ModularRouting\\Metadata\\ModuleMetadata');
        $metadata = $r->newInstanceArgs(['Test', 'test', []]);

        $this->assertEquals($metadata, $this->factory->getMetadataFor('test'));
    }

    public function testUnconfiguredMetadata()
    {
        $this->loader->expects($this->once())
            ->method('load')->with('routing.yml', 'ResourceType')
            ->will($this->returnValue([]));

        $this->assertFalse($this->factory->hasMetadataFor('test'));

        $this->setExpectedException('Harmony\\Component\\ModularRouting\\Metadata\\NoSuchMetadataException');

        $this->factory->getMetadataFor('test');
    }
}
