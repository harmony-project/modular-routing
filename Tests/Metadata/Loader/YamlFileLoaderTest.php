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

use Harmony\Component\ModularRouting\Metadata\Loader\YamlFileLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;

class YamlFileLoaderTest extends TestCase
{
    public function testSupports()
    {
        $loader = new YamlFileLoader($this->createMock('Symfony\Component\Config\FileLocator'));

        $this->assertTrue($loader->supports('foo.yml'), '->supports() returns true if the resource is loadable');
        $this->assertTrue($loader->supports('foo.yaml'), '->supports() returns true if the resource is loadable');
        $this->assertFalse($loader->supports('foo.foo'), '->supports() returns true if the resource is loadable');

        $this->assertTrue($loader->supports('foo.yml', 'yaml'), '->supports() checks the resource type if specified');
        $this->assertTrue($loader->supports('foo.yaml', 'yaml'), '->supports() checks the resource type if specified');
        $this->assertFalse($loader->supports('foo.yml', 'foo'), '->supports() checks the resource type if specified');
    }

    public function testLoadDoesNothingIfEmpty()
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../../Fixtures')));

        $this->assertEquals([], $loader->load('empty.yml'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getPathsToInvalidFiles
     */
    public function testLoadThrowsExceptionWithInvalidFile($filePath)
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../../Fixtures')));
        $loader->load($filePath);
    }

    public function getPathsToInvalidFiles()
    {
        return [
            ['nonexistent.yml'],
            ['invalid_keys.yml'],
            ['bad_format.yml'],
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

        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../../Fixtures')));

        $this->assertEquals($collection, $loader->load('valid.yml'));
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

        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../../Fixtures')));

        $this->assertEquals($collection, $loader->load('import.yml'));
    }
}
