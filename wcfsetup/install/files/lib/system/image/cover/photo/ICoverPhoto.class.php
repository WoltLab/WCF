<?php

namespace wcf\system\image\cover\photo;

/**
 * Default interface for cover photos that support mulitple sizes.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
interface ICoverPhoto
{
    public function getUrl(?string $size = null): string;

    public function getWidth(?string $size = null): int;

    public function getHeight(?string $size = null): int;

    public function getFileSize(?string $size = null): int;

    public function getMimeType(?string $size = null): string;
}
