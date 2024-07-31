<?php

namespace wcf\system\image\adapter;

/**
 * Basic interface for all image adapters.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
interface ISingleFrameImageAdapter
{
    /**
     * Loads the file by evaluating the first frame only. This is important for
     * adapters that are aware of multiframe images and parse each of them on
     * startup.
     */
    public function loadSingleFrameFromFile(string $filename): void;
}
