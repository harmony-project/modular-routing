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

use Harmony\Component\ModularRouting\Manager\StaticModuleManager;

/**
 * Simple implementation of {@link ModuleInterface} using the module type as
 * its identity. Can be used with {@link StaticModuleManager}.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class StaticModule implements ModuleInterface
{
    /**
     * @var string
     */
    protected $modularType;

    /**
     * Returns a value to identify the module.
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
