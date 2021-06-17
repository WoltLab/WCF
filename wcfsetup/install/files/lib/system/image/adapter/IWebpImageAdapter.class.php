<?php

namespace wcf\system\image\adapter;

/**
 * A WebP capable image adapter exposes a helper method to verify the
 * support for the creation and processing of WebP images.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Image\Adapter
 * @since 5.4
 */
interface IWebpImageAdapter extends IImageAdapter
{
    /**
     * Reports the ability to create and process WebP images.
     */
    public static function supportsWebp(): bool;
}
