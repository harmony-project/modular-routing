<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Repository;

use Harmony\Component\ModularRouting\ModuleInterface;
use Harmony\Component\ModularRouting\StaticModule;

/**
 * Adds useful repository methods for modular entities.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
trait ModularRepositoryTrait
{
    /**
     * Checks if a {@link ModuleInterface} instance is valid.
     *
     * By implementing this method, repositories can simply check if the module
     * is invalid to determine that there's no active association between this
     * entity and modules. Check this to change behavior within the repository
     * for projects with {@link StaticModule} modules or haven't implemented
     * modular routing.
     *
     * @param ModuleInterface|null $module
     *
     * @return boolean Returns false if the instance is invalid or not modular
     */
    protected function isModular(ModuleInterface $module = null)
    {
        if ($module instanceof ModuleInterface && !$module instanceof StaticModule) {
            return true;
        }

        return false;
    }
}
