<?php
/*
 * This file is part of the Harmony package.
 *
 * (c) Tim Goudriaan <tim@harmony-project.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Harmony\Component\ModularRouting\Metadata;

/**
 * ModuleMetadataInterface is implemented by objects that hold the metadata
 * configuration for modules.
 *
 * @author Tim Goudriaan <tim@codedmonkey.com>
 */
interface ModuleMetadataInterface
{
    /**
     * Returns the name of the metadata
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the type value of the metadata
     *
     * @return string
     */
    public function getType();

    /**
     * Returns a collection of routing resources
     *
     * @return array
     */
    public function getRouting();
}
