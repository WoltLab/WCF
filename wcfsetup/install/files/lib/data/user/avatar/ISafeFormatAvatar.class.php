<?php

namespace wcf\data\user\avatar;

/**
 * A safe avatar supports a broadly supported fallback image format.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Avatar
 */
interface ISafeFormatAvatar extends IUserAvatar
{
    /**
     * @see IUserAvatar::getURL()
     */
    public function getSafeURL(?int $size = null): string;

    /**
     * @see IUserAvatar::getImageTag()
     */
    public function getSafeImageTag(?int $size = null): string;
}
