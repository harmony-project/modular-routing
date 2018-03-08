<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting;

/**
 * Trait implementing {@link ModuleInterface} methods.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
trait ModuleTrait
{
    /**
     * @var string
     */
    protected $modularType;

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
