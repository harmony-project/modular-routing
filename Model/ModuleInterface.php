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
 * ModuleInterface
 *
 * @author Tim Goudriaan <tim@codedmonkey.com>
 */
interface ModuleInterface
{
    /**
     * Returns the type of related metadata
     *
     * @return string
     */
    public function getType();

    /**
     * Set the type of related metadata
     *
     * @param string $type
     */
    public function setType($type);
}
