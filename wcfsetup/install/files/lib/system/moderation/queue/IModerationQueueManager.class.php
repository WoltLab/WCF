<?php

namespace wcf\system\moderation\queue;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\page\IPage;

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
     * @param ModerationQueue[] $queues
     * @return void
     */
    public function assignQueues(int $objectTypeID, array $queues);

    /**
     * Returns true if given object type is valid, optionally checking object id.
     *
     * @return bool
     */
    public function isValid(string $objectType, ?int $objectID = null);

    /**
     * Returns link for viewing/editing objects for this moderation type.
     *
     * @return string
     */
    public function getLink(int $queueID);

    /**
     * Returns object type id for given object type.
     *
     * @return int
     */
    public function getObjectTypeID(string $objectType);

    /**
     * Returns object type processor by object type.
     *
     * @return IModerationQueueHandler
     */
    public function getProcessor(?string $objectType, ?int $objectTypeID = null);

    /**
     * Populates object properties for viewing.
     *
     * @param ViewableModerationQueue[] $objects
     * @return void
     */
    public function populate(int $objectTypeID, array $objects);

    /**
     * Returns whether the affected content may be removed.
     *
     * @return bool
     */
    public function canRemoveContent(ModerationQueue $queue);

    /**
     * Removes affected content. It is up to the processing object to use a
     * soft-delete or remove the content permanently.
     *
     * @return void
     */
    public function removeContent(ModerationQueue $queue, string $message = '');

    /**
     * Returns the controller class that can be used to view/edit the moderation type
     *
     * @return class-string<IPage>
     */
    public function getController(): string;
}
