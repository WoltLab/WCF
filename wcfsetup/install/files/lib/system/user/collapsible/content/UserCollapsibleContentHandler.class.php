<?php

namespace wcf\system\user\collapsible\content;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\SingletonFactory;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Provides methods for handling collapsible containers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserCollapsibleContentHandler extends SingletonFactory
{
    /**
     * object type cache
     * @var mixed[][]
     */
    protected $cache;

    /**
     * list of collapsed object ids per object type id
     * @var int[][]
     */
    protected $collapsedContent = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->cache = [
            'objectTypes' => [],
            'objectTypeIDs' => [],
        ];

        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.collapsibleContent');
        foreach ($objectTypes as $objectType) {
            $this->cache['objectTypes'][$objectType->objectTypeID] = $objectType;
            $this->cache['objectTypeIDs'][$objectType->objectType] = $objectType->objectTypeID;
        }
    }

    /**
     * Returns true if given object is collapsed.
     *
     * @param string $objectType
     * @param string $objectID
     * @return  bool
     * @throws  InvalidObjectTypeException
     */
    public function isCollapsed($objectType, $objectID)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);
        if ($objectTypeID === null) {
            throw new InvalidObjectTypeException($objectType, 'com.woltlab.wcf.collapsibleContent');
        }

        return \in_array($objectID, $this->getCollapsedContent($objectTypeID));
    }

    /**
     * Returns the object type id based upon specified object type name. Returns
     * null, if object type is unknown.
     *
     * @param string $objectType
     * @return  int|null
     */
    public function getObjectTypeID($objectType)
    {
        return $this->cache['objectTypeIDs'][$objectType] ?? null;
    }

    /**
     * Returns a list of object ids being collapsed by current user.
     *
     * @param int $objectTypeID
     * @return  int[]
     */
    public function getCollapsedContent($objectTypeID)
    {
        if (!isset($this->collapsedContent[$objectTypeID])) {
            $this->collapsedContent[$objectTypeID] = [];

            if (WCF::getUser()->userID) {
                $data = UserStorageHandler::getInstance()->getField('collapsedContent-' . $objectTypeID);

                // cache does not exist or is outdated
                if ($data === null) {
                    $sql = "SELECT  objectID
                            FROM    wcf1_user_collapsible_content
                            WHERE   objectTypeID = ?
                                AND userID = ?";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute([
                        $objectTypeID,
                        WCF::getUser()->userID,
                    ]);
                    $this->collapsedContent[$objectTypeID] = $statement->fetchAll(\PDO::FETCH_COLUMN);

                    // update storage data
                    UserStorageHandler::getInstance()->update(
                        WCF::getUser()->userID,
                        'collapsedContent-' . $objectTypeID,
                        \serialize($this->collapsedContent[$objectTypeID])
                    );
                } else {
                    $this->collapsedContent[$objectTypeID] = @\unserialize($data);
                }
            } else {
                $collapsedContent = WCF::getSession()->getVar('collapsedContent');
                if ($collapsedContent !== null && \is_array($collapsedContent)) {
                    if (isset($collapsedContent[$objectTypeID])) {
                        $this->collapsedContent[$objectTypeID] = $collapsedContent[$objectTypeID];
                    }
                }
            }
        }

        return $this->collapsedContent[$objectTypeID];
    }

    /**
     * Marks content as collapsed.
     */
    public function markAsCollapsed(int $objectTypeID, string $objectID, ?int $userID = null): void
    {
        if ($userID === null) {
            $userID = WCF::getUser()->userID;
        }

        if ($userID) {
            $sql = "SELECT  *
                    FROM    wcf1_user_collapsible_content
                    WHERE   objectTypeID = ?
                        AND objectID = ?
                        AND userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $objectTypeID,
                $objectID,
                $userID,
            ]);
            $row = $statement->fetchArray();

            if (!$row) {
                $sql = "INSERT INTO wcf1_user_collapsible_content
                                    (objectTypeID, objectID, userID)
                        VALUES      (?, ?, ?)";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $objectTypeID,
                    $objectID,
                    $userID,
                ]);
            }

            // reset storage
            UserStorageHandler::getInstance()->reset([$userID], 'collapsedContent-' . $objectTypeID);
        } else {
            $collapsedContent = WCF::getSession()->getVar('collapsedContent');
            if ($collapsedContent === null || !\is_array($collapsedContent)) {
                $collapsedContent = [];
            }

            if (!\in_array($objectID, $collapsedContent)) {
                $collapsedContent[$objectTypeID] = [];
            }

            $collapsedContent[$objectTypeID][] = $objectID;
            WCF::getSession()->register('collapsedContent', $collapsedContent);
        }
    }

    /**
     * Marks content as opened, thus removing the collapsed marking.
     */
    public function markAsOpened(int $objectTypeID, string $objectID, ?int $userID = null): void
    {
        if ($userID === null) {
            $userID = WCF::getUser()->userID;
        }

        if ($userID) {
            $sql = "DELETE FROM wcf1_user_collapsible_content
                    WHERE       objectTypeID = ?
                            AND objectID = ?
                            AND userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $objectTypeID,
                $objectID,
                $userID,
            ]);

            // reset storage
            UserStorageHandler::getInstance()->reset([$userID], 'collapsedContent-' . $objectTypeID);
        } else {
            $collapsedContent = WCF::getSession()->getVar('collapsedContent');
            if ($collapsedContent === null || !\is_array($collapsedContent)) {
                $collapsedContent = [];
            }

            if (isset($collapsedContent[$objectTypeID])) {
                foreach ($collapsedContent[$objectTypeID] as $index => $collapsedObjectID) {
                    if ($collapsedObjectID == $objectID) {
                        unset($collapsedContent[$objectTypeID][$index]);
                    }
                }
            }

            WCF::getSession()->register('collapsedContent', $collapsedContent);
        }
    }

    /**
     * Deletes all saved states for a specific object type.
     *
     * @param int $objectTypeID
     */
    public function reset($objectTypeID)
    {
        if (WCF::getUser()->userID) {
            $sql = "DELETE FROM wcf1_user_collapsible_content
                    WHERE       objectTypeID = ?
                            AND userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $objectTypeID,
                WCF::getUser()->userID,
            ]);

            // reset storage
            UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'collapsedContent-' . $objectTypeID);
        } else {
            $collapsedContent = WCF::getSession()->getVar('collapsedContent');
            if ($collapsedContent === null || !\is_array($collapsedContent)) {
                $collapsedContent = [];
            }

            if (isset($collapsedContent[$objectTypeID])) {
                unset($collapsedContent[$objectTypeID]);
            }

            WCF::getSession()->register('collapsedContent', $collapsedContent);
        }
    }

    /**
     * Deletes the saved states for a specific object or all objects of a
     * specific object type for all users.
     *
     * @param string $objectType
     * @param int $objectID
     * @throws  InvalidObjectTypeException
     */
    public function resetAll($objectType, $objectID = null)
    {
        $objectTypeID = $this->getObjectTypeID($objectType);
        if (!$objectTypeID) {
            throw new InvalidObjectTypeException($objectType, 'com.woltlab.wcf.collapsibleContent');
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('objectTypeID = ?', [$objectTypeID]);
        if ($objectID) {
            $conditionBuilder->add('objectID = ?', [$objectID]);
        }

        $sql = "DELETE FROM wcf1_user_collapsible_content
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        UserStorageHandler::getInstance()->resetAll('collapsedContent-' . $objectTypeID);
    }
}
