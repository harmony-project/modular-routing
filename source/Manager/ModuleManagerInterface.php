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
 * Interface for module managers.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
interface ModuleManagerInterface
{
    /**
     * Sets a reference to the current module.
     * 
     * @param ModuleInterface|null $module
     */
    public function setCurrentModule(ModuleInterface $module = null);

    /**
     * Returns a reference to the current module.
     * 
     * @return ModuleInterface|null
     */
    public function getCurrentModule();

    /**
     * Sets the value to use to identify a module.
     *
     * @param string $identifier
     */
    public function setModularIdentifier($identifier);

    /**
     * Returns the value to use to identify a module.
     *
     * @return string|null
     */
    public function getModularIdentifier();

    /**
     * Finds a single Module entity by a set of criteria.
     *
     * @param array $criteria The criteria
     *
     * @return ModuleInterface|null
     */
    public function findModuleBy(array $criteria);

    /**
     * Finds a single Module entity by its identity.
     *
     * @param mixed $identity The identity
     *
     * @return ModuleInterface|null
     */
    public function findModuleByIdentity($identity);
}
