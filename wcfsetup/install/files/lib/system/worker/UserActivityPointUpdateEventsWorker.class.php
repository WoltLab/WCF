<?php

namespace wcf\system\worker;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating user activity point events.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserActivityPointUpdateEventsWorker extends AbstractWorker
{
    /**
     * @inheritDoc
     */
    protected $limit = 1;

    /**
     * object types
     * @var ObjectType[]
     */
    public $objectTypes = [];

    /**
     * @inheritDoc
     */
    public function __construct(array $parameters)
    {
        parent::__construct($parameters);

        $this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.activityPointEvent');
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        WCF::getSession()->checkPermissions(['admin.user.canEditActivityPoints']);
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        $this->count = \count($this->objectTypes);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $i = 0;
        foreach ($this->objectTypes as $objectType) {
            if ($i == $this->loopCount) {
                $sql = "UPDATE  wcf1_user_activity_point
                        SET     activityPoints = items * ?
                        WHERE   objectTypeID = ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $objectType->points,
                    $objectType->objectTypeID,
                ]);
            }

            $i++;
        }
    }

    /**
     * @inheritDoc
     */
    public function getProceedURL()
    {
        return LinkHandler::getInstance()->getLink('UserActivityPointOption');
    }
}
