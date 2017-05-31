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

interface ModularInterface
{
    /**
     * @param ModuleInterface|null $module
     *
     * @return self
     */
    public function setModule(ModuleInterface $module = null);

    /**
     * @return ModuleInterface
     */
    public function getModule();
}
