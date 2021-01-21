<?php

namespace wcf\data\like;

use wcf\data\like\object\ILikeObject;
use wcf\data\object\type\IObjectTypeProvider;

/**
 * Default interface for like object type providers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Like
 */
interface ILikeObjectTypeProvider extends IObjectTypeProvider
{
    /**
     * Returns true if the active user can access the given likeable object.
     *
     * @param   ILikeObject $object
     * @return  bool
     */
    public function checkPermissions(ILikeObject $object);
}
