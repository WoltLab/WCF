<?php

namespace wcf\system\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;

/**
 * Default interface for moderation queue managers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IModerationQueueManager
{
    /**
     * Creates queue assignments for matching object type ids.
     *
     * @param int $objectTypeID
     * @param ModerationQueue[] $queues
     */
    public function assignQueues($objectTypeID, array $queues);

    /**
     * Returns true if given object type is valid, optionally checking object id.
     *
     * @param string $objectType
     * @param int $objectID
     * @return  bool
     */
    public function isValid($objectType, $objectID = null);

    /**
     * Returns link for viewing/editing objects for this moderation type.
     *
     * @param int $queueID
     * @return  string
     */
    public function getLink($queueID);

    /**
     * Returns object type id for given object type.
     *
     * @param string $objectType
     * @return  int
     */
    public function getObjectTypeID($objectType);

    /**
     * Returns object type processor by object type.
     *
     * @param string $objectType
     * @param int $objectTypeID
     * @return  object
     */
    public function getProcessor($objectType, $objectTypeID = null);

    /**
     * Populates object properties for viewing.
     *
     * @param int $objectTypeID
     * @param ViewableModerationQueue[] $objects
     */
    public function populate($objectTypeID, array $objects);

    /**
     * Returns whether the affected content may be removed.
     *
     * @param ModerationQueue $queue
     * @return  bool
     */
    public function canRemoveContent(ModerationQueue $queue);

    /**
     * Removes affected content. It is up to the processing object to use a
     * soft-delete or remove the content permanently.
     *
     * @param ModerationQueue $queue
     * @param string $message
     */
    public function removeContent(ModerationQueue $queue, $message = '');
}
