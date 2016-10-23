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
 * StaticModule
 *
 * @author Tim Goudriaan <tim@harmony-project.io>
 */
class StaticModule extends Module
{
    /**
     * {@inheritdoc}
     */
    public function getModularIdentity()
    {
        return $this->getModularType();
    }
}
