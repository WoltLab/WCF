<?php

namespace wcf\system\image\adapter;

/**
 * A memory aware image adapter is able to determine whether it is
 * likely able to process an image within the process' memory limit.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Image\Adapter
 * @since   5.3
 */
interface IMemoryAwareImageAdapter extends IImageAdapter
{
    /**
     * Returns whether it is believed that sufficient memory
     * is available to process an image with the given properties.
     *
     * @param   int     $width
     * @param   int     $height
     * @param   string      $mimeType
     * @return  bool
     */
    public function checkMemoryLimit($width, $height, $mimeType);
}
