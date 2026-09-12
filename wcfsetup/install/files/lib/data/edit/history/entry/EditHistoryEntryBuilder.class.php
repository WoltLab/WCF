<?php

namespace wcf\data\edit\history\entry;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\object\type\ObjectType;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting edit history entries.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<EditHistoryEntry>
 */
final class EditHistoryEntryBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the object type of the edited object, must be an object type of the
     * definition `com.woltlab.wcf.edit.historySavingObject`.
     */
    public function setObjectType(ObjectType $objectType): static
    {
        return $this->setObjectTypeID($objectType->objectTypeID);
    }

    /**
     * Sets the id of the object type of the edited object.
     */
    public function setObjectTypeID(int $objectTypeID): static
    {
        $this->properties['objectTypeID'] = $objectTypeID;

        return $this;
    }

    /**
     * Sets the id of the edited object.
     */
    public function setObjectID(int $objectID): static
    {
        $this->properties['objectID'] = $objectID;

        return $this;
    }

    /**
     * Sets the user who has created the previous version of the object.
     */
    public function setUser(User $user): static
    {
        $this->properties['userID'] = $user->userID;
        $this->properties['username'] = $user->username;

        return $this;
    }

    /**
     * Sets the id of the user who has created the previous version of the object.
     */
    public function setUserID(?int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    /**
     * Sets the name of the user who has created the previous version of the object.
     */
    public function setUsername(string $username): static
    {
        $this->properties['username'] = $username;

        return $this;
    }

    /**
     * Sets the timestamp at which the previous version has been created.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the timestamp at which the previous version has been replaced by the
     * edited version.
     */
    public function setObsoletedAt(int $obsoletedAt): static
    {
        $this->properties['obsoletedAt'] = $obsoletedAt;

        return $this;
    }

    /**
     * Sets the user who has replaced the previous version of the object.
     */
    public function setObsoletedByUser(User $user): static
    {
        return $this->setObsoletedByUserID($user->userID);
    }

    /**
     * Sets the id of the user who has replaced the previous version of the object.
     */
    public function setObsoletedByUserID(?int $obsoletedByUserID): static
    {
        $this->properties['obsoletedByUserID'] = $obsoletedByUserID;

        return $this;
    }

    /**
     * Sets the message of the object prior to the edit.
     */
    public function setMessage(?string $message): static
    {
        $this->properties['message'] = $message;

        return $this;
    }

    /**
     * Sets the reason for the edit.
     */
    public function setEditReason(?string $editReason): static
    {
        $this->properties['editReason'] = $editReason;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['objectTypeID', 'objectID', 'time', 'obsoletedAt'];
    }

    /**
     * Deletes the entries of the given objects of the given object type.
     *
     * @param list<int> $objectIDs
     */
    public static function deleteByObjectIDs(int $objectTypeID, array $objectIDs): void
    {
        if ($objectIDs === []) {
            return;
        }

        $itemsPerLoop = 1000;
        $loopCount = \ceil(\count($objectIDs) / $itemsPerLoop);

        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            for ($i = 0; $i < $loopCount; $i++) {
                $batchObjectIDs = \array_slice($objectIDs, $i * $itemsPerLoop, $itemsPerLoop);

                $conditionBuilder = new PreparedStatementConditionBuilder();
                $conditionBuilder->add('objectTypeID = ?', [$objectTypeID]);
                $conditionBuilder->add('objectID IN (?)', [$batchObjectIDs]);

                $sql = "DELETE FROM " . EditHistoryEntry::getDatabaseTableName() . "
                        " . $conditionBuilder;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditionBuilder->getParameters());
            }
            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }
    }

    /**
     * Deletes all entries that have been obsoleted before the given timestamp.
     */
    public static function deleteObsoletedBefore(int $timestamp): void
    {
        $sql = "DELETE FROM " . EditHistoryEntry::getDatabaseTableName() . "
                WHERE       obsoletedAt < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$timestamp]);
    }

    /**
     * Deletes all entries.
     */
    public static function clearAll(): void
    {
        $sql = "DELETE FROM " . EditHistoryEntry::getDatabaseTableName();
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
    }
}
