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
 * Adds useful repository methods for modular entities.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
trait ModularRepositoryTrait
{
    /**
     * Checks if a Module instance is valid.
     *
     * By implementing this method, repositories can simply check if the Module instance is invalid
     * to determine that it doesn't have an association with the given Module. Enabling the use of
     * the entity with StaticModule instances or without implementing the ModularRouting component.
     *
     * @param ModuleInterface|null $module
     *
     * @return bool Returns false if the instance is invalid or not modular
     */
    protected function isModular(ModuleInterface $module = null)
    {
        if (null == $module) {
            return false;
        }

        if ($module instanceof StaticModule) {
            return false;
        }

        return true;
    }
}
