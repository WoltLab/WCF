<?php

namespace wcf\system\worker;

use wcf\data\DatabaseObjectList;
use wcf\data\user\group\UserGroup;
use wcf\system\cache\builder\UserGroupPermissionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Abstract implementation of rebuild data worker.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractRebuildDataWorker extends AbstractWorker implements IRebuildDataWorker
{
    /**
     * class name for DatabaseObjectList
     * @var string
     */
    protected $objectListClassName = '';

    /**
     * database object list
     * @var DatabaseObjectList
     */
    protected $objectList;

    /**
     * @inheritDoc
     */
    public function getObjectList()
    {
        return $this->objectList;
    }

    /**
     * @inheritDoc
     */
    public function setLoopCount($loopCount)
    {
        parent::setLoopCount($loopCount);

        $this->initObjectList();
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        WCF::getSession()->checkPermissions(['admin.management.canRebuildData']);
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        if ($this->count === null) {
            if ($this->objectList === null) {
                $this->initObjectList();
            }

            $this->count = $this->objectList->countObjects();
        }
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->objectList->readObjects();

        SearchIndexManager::getInstance()->beginBulkOperation();

        EventHandler::getInstance()->fireAction($this, 'execute');
    }

    /**
     * @inheritDoc
     */
    public function getProceedURL()
    {
        return LinkHandler::getInstance()->getLink('RebuildData');
    }

    /**
     * Initializes DatabaseObjectList instance.
     */
    protected function initObjectList()
    {
        if (empty($this->objectListClassName)) {
            throw new SystemException('DatabaseObjectList class name not specified.');
        }

        if (!\is_subclass_of($this->objectListClassName, DatabaseObjectList::class)) {
            throw new ParentClassException($this->objectListClassName, DatabaseObjectList::class);
        }

        $this->objectList = new $this->objectListClassName();
        $this->objectList->sqlLimit = $this->limit;
        $this->objectList->sqlOffset = $this->limit * $this->loopCount;
    }

    /**
     * Returns the value of the permissions for the provided user ids. The special index `0` is
     * automatically added and represents a guest user.
     *
     * @param int[] $userIDs
     * @param string[] $permissions
     * @return      mixed[]         permission value per user id
     */
    protected function getBulkUserPermissions(array $userIDs, array $permissions)
    {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$userIDs]);

        $sql = "SELECT  userID, groupID
                FROM    wcf1_user_to_group
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        $groupData = [];
        while ($row = $statement->fetchArray()) {
            $userID = $row['userID'];
            if (!isset($groupData[$userID])) {
                $groupData[$userID] = [];
            }

            $groupData[$userID][] = $row['groupID'];
        }

        $userPermissions = [];
        foreach ($groupData as $userID => $groupIDs) {
            $data = UserGroupPermissionCacheBuilder::getInstance()->getData($groupIDs);

            $userPermissions[$userID] = [];
            foreach ($permissions as $permission) {
                $userPermissions[$userID][$permission] = (isset($data[$permission])) ? $data[$permission] : false;
            }
        }

        // add guest user
        $data = UserGroupPermissionCacheBuilder::getInstance()
            ->getData(UserGroup::getGroupIDsByType([UserGroup::GUESTS, UserGroup::EVERYONE]));
        $userPermissions[0] = [];
        foreach ($permissions as $permission) {
            $userPermissions[0][$permission] = (isset($data[$permission])) ? $data[$permission] : false;
        }

        return $userPermissions;
    }

    /**
     * Returns the permission value for the provided user id, will be treated as guest
     * if the user id cannot be found or is invalid. This method is designed to be used
     * with the return value of `getBulkUserPermissions()`.
     *
     * @param mixed[] $userPermissions
     * @param int $userID
     * @param string $permission
     * @return      mixed
     */
    protected function getBulkUserPermissionValue(array &$userPermissions, $userID, $permission)
    {
        $userID = \intval($userID);

        // resolve non-existing users against the guest permission
        if ($userID && !isset($userPermissions[$userID])) {
            return $this->getBulkUserPermissionValue($userPermissions, 0, $permission);
        }

        return $userPermissions[$userID][$permission];
    }

    /**
     * @inheritDoc
     */
    public function finalize()
    {
        SearchIndexManager::getInstance()->commitBulkOperation();
        UserStorageHandler::getInstance()->shutdown();
    }
}
