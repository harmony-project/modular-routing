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
 * Interface implemented by modules.
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
interface ModuleInterface
{
    /**
     * Returns a value to identify the module.
     *
     * @return mixed
     */
    public function getModularIdentity();

    /**
     * Sets the type of the module.
     *
     * @param string $type
     */
    public function setModularType($type);

    /**
     * Returns the type of the module.
     *
     * @return string
     */
    public function getModularType();
}
