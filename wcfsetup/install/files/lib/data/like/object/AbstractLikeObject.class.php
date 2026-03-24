<?php

namespace wcf\data\like\object;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\like\Like;
use wcf\data\object\type\ObjectType;

/**
 * Provides a default implementation for like objects.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   LikeObject
 * @template TDatabaseObject of DatabaseObject
 * @extends DatabaseObjectDecorator<TDatabaseObject>
 */
abstract class AbstractLikeObject extends DatabaseObjectDecorator implements ILikeObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = LikeObject::class;

    /**
     * object type
     * @var ObjectType
     */
    protected $objectType;

    #[\Override]
    public function updateLikeCounter(int $cumulativeLikes)
    {
        // individual implementations can override this method to update like counter
    }

    #[\Override]
    public function getObjectType()
    {
        return $this->objectType;
    }

    #[\Override]
    public function setObjectType(ObjectType $objectType)
    {
        $this->objectType = $objectType;
    }

    #[\Override]
    public function sendNotification(Like $like)
    {
        // individual implementations can override this method to provide notifications
    }

    #[\Override]
    public function getLanguageID()
    {
        return null;
    }
}
