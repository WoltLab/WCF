<?php

namespace wcf\data\user\cover\photo;

/**
 * Any displayable cover photo type should implement this class.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Cover\Photo
 * @since 5.4
 */
interface IWebpUserCoverPhoto extends IUserCoverPhoto
{
    /**
     * @return null|bool
     */
    public function createWebpVariant();
}
