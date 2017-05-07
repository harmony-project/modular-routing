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
 * To be embedded in entities that are mapped to a module.
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
     * @return ModuleInterface
     */
    public function getModule()
    {
        return $this->module;
    }
}
