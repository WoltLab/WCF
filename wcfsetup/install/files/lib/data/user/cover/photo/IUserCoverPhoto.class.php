<?php

namespace wcf\data\user\cover\photo;

/**
 * Any displayable cover photo type should implement this class.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IUserCoverPhoto
{
    /**
     * Deletes this cover photo.
     */
    public function delete();

    /**
     * Returns the physical location of this cover photo.
     */
    public function getLocation(?bool $forceWebP = null): string;

    /**
     * Returns the url to this cover photo.
     */
    public function getURL(?bool $forceWebP = null): string;

    /**
     * Returns the file name of this cover photo.
     */
    public function getFilename(?bool $forceWebP = null): string;
}
