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
use Harmony\Component\ModularRouting\Model\StaticModule;

/**
 * A module manager that creates modules during runtime.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class StaticModuleManager extends ModuleManager implements ModuleManagerInterface
{
    /**
     * @var array
     */
    private $modules = [];

    /**
     * {@inheritdoc}
     */
    public function setModularIdentifier($identifier)
    {
        throw new \BadMethodCallException(sprintf('The modular identifier of %s can\'t be changed.', static::class));
    }

    /**
     * {@inheritdoc}
     */
    public function getModularIdentifier()
    {
        return 'modularType';
    }

    /**
     * {@inheritdoc}
     */
    public function findModuleBy(array $criteria)
    {
        $identifier = $this->getModularIdentifier();

        if (isset($criteria[$identifier])) {
            return $this->findModuleByIdentity($criteria[$identifier]);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findModuleByIdentity($identity)
    {
        if (isset($this->modules[$identity])) {
            return $this->modules[$identity];
        }

        $module = new StaticModule;
        $module->setModularType($identity);

        return $this->modules[$identity] = $module;
    }
}
