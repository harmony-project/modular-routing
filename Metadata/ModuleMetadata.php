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

/**
 * ModuleMetadata
 *
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
     * @var array
     */
    protected $routing;

    /**
     * @param string $name
     * @param string $type
     * @param array  $routing
     */
    public function __construct($name, $type, array $routing = [])
    {
        $this->name     = $name;
        $this->type     = $type;
        $this->routing  = $routing;
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
    public function getRouting()
    {
        return $this->routing;
    }
}
