<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Model;

/**
 * Simple implementation of ModuleInterface using the module type as its identity.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class StaticModule extends Module
{
    /**
     * @var string
     */
    protected $modularType;

    /**
     * Returns the identity of the module.
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->modularType;
    }

    /**
     * {@inheritdoc}
     */
    public function getModularIdentity()
    {
        return $this->modularType;
    }

    /**
     * {@inheritdoc}
     */
    public function setModularType($type)
    {
        $this->modularType = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModularType()
    {
        return $this->modularType;
    }
}
