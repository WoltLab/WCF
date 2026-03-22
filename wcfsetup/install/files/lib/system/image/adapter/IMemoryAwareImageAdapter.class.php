<?php

namespace wcf\system\image\adapter;

/**
 * A memory aware image adapter is able to determine whether it is
 * likely able to process an image within the process' memory limit.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.3
 * @template T of object
 * @extends IImageAdapter<T>
 */
interface IMemoryAwareImageAdapter extends IImageAdapter
{
    /**
     * Returns whether it is believed that sufficient memory
     * is available to process an image with the given properties.
     *
     * @return  bool
     */
    public function checkMemoryLimit(int $width, int $height, string $mimeType);
}
