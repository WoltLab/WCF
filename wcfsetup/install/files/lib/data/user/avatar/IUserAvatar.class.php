<?php

namespace wcf\data\user\avatar;

/**
 * Any displayable avatar type should implement this class.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IUserAvatar
{
    /**
     * Returns the url to this avatar.
     *
     * @return  string
     */
    public function getURL(?int $size = null);

    /**
     * Returns the html code to display this avatar.
     *
     * @return  string
     */
    public function getImageTag(?int $size = null);

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
