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
 * Trait implementing {@link ModularInterface} methods.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
trait ModularTrait
{
    /**
     * @var ModuleInterface
     */
    protected $module;

    /**
     * Sets the associated module.
     *
     * @param ModuleInterface|null $module
     *
     * @return self
     */
    public function setModule(ModuleInterface $module = null)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Returns the associated module.
     *
     * @return ModuleInterface
     */
    public function getModule()
    {
        return $this->module;
    }
}
