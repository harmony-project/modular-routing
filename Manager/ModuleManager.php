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
 * ModuleManager
 *
 * Interacts with Module entities.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
abstract class ModuleManager implements ModuleManagerInterface
{
    /**
     * Intermediary reference for applications to keep track of the module
     * that was referenced in the Request
     *
     * @var ModuleInterface|null
     */
    private $currentModule = null;

    /**
     * An optional value a ModuleManager can use to identify a module
     *
     * @var string|null
     */
    private $modularIdentifier = null;

    /**
     * {@inheritdoc}
     */
    public function setCurrentModule(ModuleInterface $module = null)
    {
        $this->currentModule = $module;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentModule()
    {
        return $this->currentModule;
    }

    /**
     * {@inheritdoc}
     */
    public function setModularIdentifier($identifier)
    {
        $this->modularIdentifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getModularIdentifier()
    {
        return $this->modularIdentifier;
    }
}
