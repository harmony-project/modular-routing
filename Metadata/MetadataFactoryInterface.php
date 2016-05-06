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
 * MetadataFactoryInterface
 *
 * Returns {@link ModuleMetadataInterface} instances for values.
 *
 * @author Tim Goudriaan <tim@codedmonkey.com>
 */
interface MetadataFactoryInterface
{
    /**
     * Returns the metadata for the given value.
     *
     * @param mixed $value The metadata for the value
     *
     * @return ModuleMetadataInterface
     * @throws NoSuchMetadataException If no metadata exists for the given valu
     */
    public function getMetadataFor($value);

    /**
     * Returns whether the class is able to return metadata for the given value.
     *
     * @param mixed $value Some value
     *
     * @return bool Whether metadata can be returned for that value
     */
    public function hasMetadataFor($value);
}
