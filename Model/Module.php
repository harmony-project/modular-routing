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
 * Basic extendable implementation of {@link ModuleInterface}.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
abstract class Module implements ModuleInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $modularType;

    /**
     * Returns the id of the module.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getModularIdentity()
    {
        return $this->getId();
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
