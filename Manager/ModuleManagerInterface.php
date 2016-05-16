<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Manager;

use Harmony\Component\ModularRouting\Model\ModuleInterface;

/**
 * ModuleManagerInterface
 *
 * Interacts with Module entities
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
interface ModuleManagerInterface
{
    /**
     * Sets a reference to the current module
     * 
     * @param ModuleInterface|null $module
     */
    public function setCurrentModule(ModuleInterface $module = null);

    /**
     * Returns a reference to the current module
     * 
     * @return ModuleInterface|null
     */
    public function getCurrentModule();

    /**
     * Finds a single Module entity by a set of criteria.
     *
     * @param array $criteria The criteria
     *
     * @return ModuleInterface|null
     */
    public function findModuleBy(array $criteria);
}
