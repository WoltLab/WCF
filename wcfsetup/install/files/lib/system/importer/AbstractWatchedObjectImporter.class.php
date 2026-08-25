<?php

namespace wcf\system\importer;

use wcf\data\user\object\watch\UserObjectWatch;
use wcf\data\user\object\watch\UserObjectWatchBuilder;

/**
 * Imports watched objects.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AbstractWatchedObjectImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = UserObjectWatch::class;

    /**
     * object type id for watched objects
     * @var int
     */
    protected $objectTypeID = 0;

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        $data['userID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID']);
        if ($data['userID'] === null) {
            return 0;
        }

        $watch = UserObjectWatchBuilder::forCreate()
            ->setObjectTypeID($this->objectTypeID)
            ->setObjectID((int)$data['objectID'])
            ->setUserID($data['userID'])
            ->setNotification((bool)($data['notification'] ?? false))
            ->createOrIgnore();

        return $watch?->watchID ?: 0;
    }
}
