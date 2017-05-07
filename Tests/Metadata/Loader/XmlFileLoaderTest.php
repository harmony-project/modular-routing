<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Tests\Metadata\Loader;

use Harmony\Component\ModularRouting\Metadata\Loader\XmlFileLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;

class XmlFileLoaderTest extends TestCase
{
    public function testSupports()
    {
        $loader = new XmlFileLoader(new FileLocator([__DIR__ . '/../../Fixtures']));

        $this->assertTrue($loader->supports('foo.xml'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports('foo.xml', 'xml'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports('foo.xml', 'foo'), '->supports() checks the resource type if specified');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getPathsToInvalidFiles
     */
    public function testLoadThrowsExceptionWithInvalidFile($filePath)
    {
        $loader = new XmlFileLoader(new FileLocator(array(__DIR__ . '/../../Fixtures')));
        $loader->load($filePath);
    }

    public function getPathsToInvalidFiles()
    {
        return [
            ['nonexistent.xml'],
            ['invalid_nodes.xml'],
        ];
    }

    public function testLoad()
    {
        $collection = [
            'module_foo' => [
                'name' => 'Foo Module',
                'type' => 'foo_module',
                'routing' => [
                    [
                        'resource' => 'FooComponent/Controller/',
                        'type'     => 'annotation',
                    ]
                ],
            ]
        ];

        $loader = new XmlFileLoader(new FileLocator([__DIR__ . '/../../Fixtures']));

        $this->assertEquals($collection, $loader->load('valid.xml'));
    }

    public function testLoadWithImport()
    {
        $collection = [
            'module_foo' => [
                'name' => 'Foo Module',
                'type' => 'foo_module',
                'routing' => [
                    [
                        'resource' => 'FooComponent/Controller/',
                        'type'     => 'annotation',
                    ]
                ],
            ]
        ];

        $loader = new XmlFileLoader(new FileLocator([__DIR__ . '/../../Fixtures']));

        $this->assertEquals($collection, $loader->load('import.xml'));
    }
}
