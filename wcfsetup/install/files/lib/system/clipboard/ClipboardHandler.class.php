<?php

namespace wcf\system\clipboard;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ClipboardActionCacheBuilder;
use wcf\system\cache\builder\ClipboardPageCacheBuilder;
use wcf\system\clipboard\action\IClipboardAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles clipboard-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ClipboardHandler extends SingletonFactory
{
    /**
     * cached list of actions
     * @var array
     */
    protected $actionCache;

    /**
     * cached list of clipboard item types
     * @var mixed[][]
     */
    protected $cache;

    /**
     * list of marked items
     * @var DatabaseObject[][]
     */
    protected $markedItems;

    /**
     * cached list of page actions
     * @var array
     */
    protected $pageCache;

    /**
     * list of page class names
     * @var string[]
     */
    protected $pageClasses = [];

    /**
     * page object id
     * @var int
     */
    protected $pageObjectID = 0;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->cache = [
            'objectTypes' => [],
            'objectTypeNames' => [],
        ];
        $cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.clipboardItem');
        foreach ($cache as $objectType) {
            $this->cache['objectTypes'][$objectType->objectTypeID] = $objectType;
            $this->cache['objectTypeNames'][$objectType->objectType] = $objectType->objectTypeID;
        }

        $this->pageCache = ClipboardPageCacheBuilder::getInstance()->getData();
    }

    /**
     * Loads action cache.
     */
    protected function loadActionCache()
    {
        if ($this->actionCache !== null) {
            return;
        }

        $this->actionCache = ClipboardActionCacheBuilder::getInstance()->getData();
    }

    /**
     * Marks objects as marked.
     *
     * @param array $objectIDs
     * @param int $objectTypeID
     */
    public function mark(array $objectIDs, $objectTypeID)
    {
        // remove existing entries first, prevents conflict with INSERT
        $this->unmark($objectIDs, $objectTypeID);

        $sql = "INSERT INTO wcf1_clipboard_item
                            (objectTypeID, userID, objectID)
                VALUES      (?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($objectIDs as $objectID) {
            $statement->execute([
                $objectTypeID,
                WCF::getUser()->userID,
                $objectID,
            ]);
        }
    }

    /**
     * Removes an object marking.
     *
     * @param array $objectIDs
     * @param int $objectTypeID
     */
    public function unmark(array $objectIDs, $objectTypeID)
    {
        if ($objectIDs === []) {
            return;
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [$objectTypeID]);
        $conditions->add("objectID IN (?)", [$objectIDs]);
        $conditions->add("userID = ?", [WCF::getUser()->userID]);

        $sql = "DELETE FROM wcf1_clipboard_item
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
    }

    /**
     * Unmarks all items of given type.
     *
     * @param int $objectTypeID
     */
    public function unmarkAll($objectTypeID)
    {
        $sql = "DELETE FROM wcf1_clipboard_item
                WHERE       objectTypeID = ?
                        AND userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            WCF::getUser()->userID,
        ]);
    }

    /**
     * Returns the id of the clipboard object type with the given name or `null` if no such
     * clipboard object type exists.
     *
     * @param string $typeName
     * @return  int|null
     */
    public function getObjectTypeID($typeName)
    {
        return $this->cache['objectTypeNames'][$typeName] ?? null;
    }

    /**
     * Returns the clipboard object type with the given id or `null` if no such
     * clipboard object type exists.
     *
     * @param int $objectTypeID
     * @return  ObjectType|null
     */
    public function getObjectType($objectTypeID)
    {
        return $this->cache['objectTypes'][$objectTypeID] ?? null;
    }

    /**
     * Returns the id of the clipboard object type with the given name or `null` if no such
     * clipboard object type exists.
     *
     * @param string $objectType
     * @return  int|null
     */
    public function getObjectTypeByName($objectType)
    {
        foreach ($this->cache['objectTypes'] as $objectTypeID => $objectTypeObj) {
            if ($objectTypeObj->objectType == $objectType) {
                return $objectTypeID;
            }
        }

        return null;
    }

    /**
     * Loads a list of marked items grouped by type name.
     *
     * @param int $objectTypeID
     * @throws  SystemException
     */
    protected function loadMarkedItems($objectTypeID = null)
    {
        if ($this->markedItems === null) {
            $this->markedItems = [];
        }

        if ($objectTypeID !== null) {
            $objectType = $this->getObjectType($objectTypeID);
            if ($objectType === null) {
                throw new SystemException("object type id " . $objectTypeID . " is invalid");
            }

            if (!isset($this->markedItems[$objectType->objectType])) {
                $this->markedItems[$objectType->objectType] = [];
            }
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID = ?", [WCF::getUser()->userID]);
        if ($objectTypeID !== null) {
            $conditions->add("objectTypeID = ?", [$objectTypeID]);
        }

        // fetch object ids
        $sql = "SELECT  objectTypeID, objectID
                FROM    wcf1_clipboard_item
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        // group object ids by type name
        $data = [];
        while ($row = $statement->fetchArray()) {
            $objectType = $this->getObjectType($row['objectTypeID']);
            if ($objectType === null) {
                continue;
            }

            if (!isset($data[$objectType->objectType])) {
                /** @noinspection PhpUndefinedFieldInspection */
                $listClassName = $objectType->listclassname;
                if ($listClassName == '') {
                    throw new SystemException("Missing list class for object type '" . $objectType->objectType . "'");
                }

                $data[$objectType->objectType] = [
                    'className' => $listClassName,
                    'objectIDs' => [],
                ];
            }

            $data[$objectType->objectType]['objectIDs'][] = $row['objectID'];
        }

        // read objects
        foreach ($data as $objectType => $objectData) {
            /** @var DatabaseObjectList $objectList */
            $objectList = new $objectData['className']();
            $objectList->getConditionBuilder()->add(
                $objectList->getDatabaseTableAlias() . "." . $objectList->getDatabaseTableIndexName() . " IN (?)",
                [$objectData['objectIDs']]
            );
            $objectList->readObjects();

            $this->markedItems[$objectType] = $objectList->getObjects();

            // validate object ids against loaded items (check for zombie object ids)
            $indexName = $objectList->getDatabaseTableIndexName();
            foreach ($this->markedItems[$objectType] as $object) {
                /** @noinspection PhpVariableVariableInspection */
                $index = \array_search($object->{$indexName}, $objectData['objectIDs']);
                unset($objectData['objectIDs'][$index]);
            }

            if (!empty($objectData['objectIDs'])) {
                $conditions = new PreparedStatementConditionBuilder();
                $conditions->add("objectTypeID = ?", [$this->getObjectTypeByName($objectType)]);
                $conditions->add("userID = ?", [WCF::getUser()->userID]);
                $conditions->add("objectID IN (?)", [$objectData['objectIDs']]);

                $sql = "DELETE FROM wcf1_clipboard_item
                        " . $conditions;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditions->getParameters());
            }
        }
    }

    /**
     * Loads a list of marked items grouped by type name.
     *
     * @param int $objectTypeID
     * @return  array
     */
    public function getMarkedItems($objectTypeID = null)
    {
        if ($this->markedItems === null) {
            $this->loadMarkedItems($objectTypeID);
        }

        if ($objectTypeID !== null) {
            $objectType = $this->getObjectType($objectTypeID);
            if (!isset($this->markedItems[$objectType->objectType])) {
                $this->loadMarkedItems($objectTypeID);
            }

            return $this->markedItems[$objectType->objectType];
        }

        return $this->markedItems;
    }

    /**
     * Returns `true` if the object with the given data is marked.
     */
    public function isMarked(int $objectTypeID, int $objectID): bool
    {
        return isset($this->getMarkedItems($objectTypeID)[$objectID]);
    }

    /**
     * Returns the data of the items for clipboard editor or `null` if no items are marked.
     *
     * @param string|string[] $page
     * @param int $pageObjectID
     * @return  array|null
     * @throws  ImplementationException
     */
    public function getEditorItems($page, $pageObjectID)
    {
        $pages = $page;
        if (!\is_array($pages)) {
            $pages = [$page];
        }

        $this->pageClasses = [];
        $this->pageObjectID = 0;

        // get objects
        $this->loadMarkedItems();
        if (empty($this->markedItems)) {
            return null;
        }

        $this->pageClasses = $pages;
        $this->pageObjectID = $pageObjectID;

        // fetch action ids
        $this->loadActionCache();
        $actionIDs = [];
        foreach ($pages as $page) {
            foreach ($this->pageCache[$page] as $actionID) {
                if (isset($this->actionCache[$actionID])) {
                    $actionIDs[] = $actionID;
                }
            }
        }
        $actionIDs = \array_unique($actionIDs);

        // load actions
        $actions = [];
        foreach ($actionIDs as $actionID) {
            $actionObject = $this->actionCache[$actionID];
            $actionClassName = $actionObject->actionClassName;
            if (!isset($actions[$actionClassName])) {
                // validate class
                if (!\is_subclass_of($actionClassName, IClipboardAction::class)) {
                    throw new ImplementationException($actionClassName, IClipboardAction::class);
                }

                $actions[$actionClassName] = [
                    'actions' => [],
                    'object' => new $actionClassName(),
                ];
            }

            $actions[$actionClassName]['actions'][] = $actionObject;
        }

        // execute actions
        $editorData = [];
        foreach ($actions as $actionData) {
            /** @var IClipboardAction $clipboardAction */
            $clipboardAction = $actionData['object'];

            // get accepted objects
            $typeName = $clipboardAction->getTypeName();
            if (!isset($this->markedItems[$typeName]) || empty($this->markedItems[$typeName])) {
                continue;
            }

            if (!isset($editorData[$typeName])) {
                $editorData[$typeName] = [
                    'label' => $clipboardAction->getEditorLabel($this->markedItems[$typeName]),
                    'items' => [],
                    'reloadPageOnSuccess' => $clipboardAction->getReloadPageOnSuccess(),
                ];
            } else {
                $editorData[$typeName]['reloadPageOnSuccess'] = \array_unique(\array_merge(
                    $editorData[$typeName]['reloadPageOnSuccess'],
                    $clipboardAction->getReloadPageOnSuccess()
                ));
            }

            foreach ($actionData['actions'] as $actionObject) {
                $data = $clipboardAction->execute($this->markedItems[$typeName], $actionObject);
                if ($data === null) {
                    continue;
                }

                $editorData[$typeName]['items'][$actionObject->showOrder] = $data;
            }
        }

        return $editorData;
    }

    /**
     * Removes items from clipboard.
     *
     * @param int $typeID
     */
    public function removeItems($typeID = null)
    {
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID = ?", [WCF::getUser()->userID]);
        if ($typeID !== null) {
            $conditions->add("objectTypeID = ?", [$typeID]);
        }

        $sql = "DELETE FROM wcf1_clipboard_item
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
    }

    /**
     * Returns true (1) if at least one item (of the given object type) is marked.
     *
     * @param int $objectTypeID
     * @return  int
     */
    public function hasMarkedItems($objectTypeID = null)
    {
        if (!WCF::getUser()->userID) {
            return 0;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add("userID = ?", [WCF::getUser()->userID]);
        if ($objectTypeID !== null) {
            $conditionBuilder->add("objectTypeID = ?", [$objectTypeID]);
        }

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_clipboard_item
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleColumn() ? 1 : 0;
    }

    /**
     * Returns the list of page class names.
     *
     * @return      string[]
     */
    public function getPageClasses()
    {
        return $this->pageClasses;
    }

    /**
     * Returns page object id.
     *
     * @return  int
     */
    public function getPageObjectID()
    {
        return $this->pageObjectID;
    }
}
