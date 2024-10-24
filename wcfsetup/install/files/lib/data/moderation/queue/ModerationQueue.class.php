<?php

namespace wcf\data\moderation\queue;

use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\WCF;

/**
 * Represents a moderation queue entry.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $queueID        unique id of the moderation queue entry
 * @property-read   int $objectTypeID       id of the `com.woltlab.wcf.moderation.type` object type
 * @property-read   int $objectID       id of the object of the object type with id `$objectTypeID` to which the moderation queue entry belongs to
 * @property-read   int $containerID        id of the object's container object to which the modification log entry belongs to or `0` if no such container object exists or is logged
 * @property-read   int|null $userID         id of the user who created the moderation queue entry or `null` if the user does not exist anymore or if the moderation queue entry has been created by a guest
 * @property-read   int $time           timestamp at which the moderation queue entry has been created
 * @property-read   int|null $assignedUserID     id of the user to which the moderation queue entry is assigned or `null` if it is not assigned to any user
 * @property-read   int $status         status of the moderation queue entry (see `ModerationQueue::STATUS_*` constants)
 * @property-read   int $comments       number of comments on the moderation queue entry
 * @property-read   int $lastChangeTime     timestamp at which the moderation queue entry has been changed the last time
 * @property-read   array $additionalData     array with additional data of the moderation queue entry
 * @property-read   bool $markAsJustified    true if the report was closed, but it was actually justified and other actions may have been taken
 */
class ModerationQueue extends DatabaseObject
{
    // states of column 'status'
    const STATUS_OUTSTANDING = 0;

    const STATUS_PROCESSING = 1;

    const STATUS_DONE = 2;

    const STATUS_REJECTED = 3;

    const STATUS_CONFIRMED = 4;

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        $value = parent::__get($name);

        // treat additional data as data variables if it is an array
        if ($value === null) {
            if (\is_array($this->data['additionalData']) && isset($this->data['additionalData'][$name])) {
                $value = $this->data['additionalData'][$name];
            }
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    protected function handleData($data)
    {
        parent::handleData($data);

        $this->data['additionalData'] = @\unserialize($this->data['additionalData']);
        if (!\is_array($this->data['additionalData'])) {
            $this->data['additionalData'] = [];
        }
    }

    /**
     * Returns true if the given user can edit this moderation queue.
     *
     * @param $user The user to check permissions for. null to use WCF::getUser().
     */
    public function canEdit(?User $user = null): bool
    {
        if ($user === null) {
            $user = WCF::getUser();
        }

        $sql = "SELECT  isAffected
                FROM    wcf1_moderation_queue_to_user
                WHERE   queueID = ?
                    AND userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->queueID,
            $user->userID,
        ]);
        $row = $statement->fetchArray();

        return $row !== false && $row['isAffected'];
    }

    /**
     * Returns true, if this queue is done.
     *
     * @return  bool
     */
    public function isDone()
    {
        return $this->status == self::STATUS_DONE || $this->status == self::STATUS_CONFIRMED || $this->status == self::STATUS_REJECTED;
    }

    /**
     * Returns status text.
     *
     * @param int $status
     * @return  string
     */
    public function getStatus($status = null)
    {
        $status = ($status === null) ? $this->status : $status;
        switch ($status) {
            case self::STATUS_OUTSTANDING:
                return WCF::getLanguage()->get('wcf.moderation.status.outstanding');
                break;

            case self::STATUS_PROCESSING:
                return WCF::getLanguage()->get('wcf.moderation.status.processing');
                break;

            case self::STATUS_DONE:
                return WCF::getLanguage()->get('wcf.moderation.status.done');
                break;

            case self::STATUS_REJECTED:
            case self::STATUS_CONFIRMED:
                $objectType = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
                $definition = ObjectTypeCache::getInstance()->getDefinition($objectType->definitionID);

                $phrase = 'confirmed';
                if ($status == self::STATUS_REJECTED) {
                    $phrase = ($this->markAsJustified) ? 'rejectedButJustified' : 'rejected';
                }

                return WCF::getLanguage()->get('wcf.moderation.status.' . $phrase . '.' . $definition->definitionName);
                break;
        }
    }

    /**
     * Returns `true` if the `justified` status can be changed.
     *
     * @since   5.4
     */
    public function canChangeJustifiedStatus(): bool
    {
        return \in_array($this->status, [self::STATUS_REJECTED, self::STATUS_CONFIRMED]);
    }
}
