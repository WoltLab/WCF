<?php

namespace wcf\data\user\avatar;

/**
 * Any displayable avatar type should implement this class.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User\Avatar
 */
interface IUserAvatar
{
    /**
     * Returns the url to this avatar.
     *
     * @param int $size
     * @return  string
     */
    public function getURL($size = null);

    /**
     * Returns the html code to display this avatar.
     *
     * @param int $size
     * @return  string
     */
    public function getImageTag($size = null);

    /**
     * Returns the width of this avatar.
     *
     * @return  int
     */
    public function getWidth();

    /**
     * Returns the height of this avatar.
     *
     * @return  int
     */
    public function getHeight();
}
