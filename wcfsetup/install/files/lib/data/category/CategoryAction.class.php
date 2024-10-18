<?php

namespace wcf\data\category;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\data\IToggleContainerAction;
use wcf\data\language\item\LanguageItemAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\system\acl\ACLHandler;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\user\collapsible\content\UserCollapsibleContentHandler;
use wcf\system\WCF;

/**
 * Executes category-related actions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Category        create()
 * @method  CategoryEditor[]    getObjects()
 * @method  CategoryEditor      getSingleObject()
 */
class CategoryAction extends AbstractDatabaseObjectAction implements
    ISortableAction,
    IToggleAction,
    IToggleContainerAction
{
    use TDatabaseObjectToggle;

    /**
     * categorized object type
     * @var \wcf\data\object\type\ObjectType
     */
    protected $objectType;

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'toggle', 'update', 'updatePosition'];

    /**
     * @inheritDoc
     */
    public function delete()
    {
        // call category types
        foreach ($this->getObjects() as $categoryEditor) {
            $categoryEditor->getProcessor()->beforeDeletion($categoryEditor);
        }

        $returnValue = parent::delete();

        // delete acl
        foreach ($this->getObjects() as $categoryEditor) {
            $aclObjectTypeName = $categoryEditor->getObjectType()->getProcessor()->getObjectTypeName('com.woltlab.wcf.acl');
            if ($aclObjectTypeName) {
                ACLHandler::getInstance()->removeValues(
                    ACLHandler::getInstance()->getObjectTypeID($aclObjectTypeName),
                    [$categoryEditor->categoryID]
                );
            }
        }

        // delete language items
        if (!empty($this->objects)) {
            // identify i18n labels
            $languageVariables = [];
            foreach ($this->getObjects() as $category) {
                if ($category->title === $category->getProcessor()->getI18nLangVarPrefix() . '.title.category' . $category->categoryID) {
                    $languageVariables[] = $category->title;
                }
                if ($category->description === $category->getProcessor()->getI18nLangVarPrefix() . '.description.category' . $category->categoryID) {
                    $languageVariables[] = $category->description;
                }
            }

            // remove language variables
            if (!empty($languageVariables)) {
                $conditions = new PreparedStatementConditionBuilder();
                $conditions->add('languageItem IN (?)', [$languageVariables]);

                $sql = "SELECT  languageItemID
                        FROM    wcf1_language_item
                        " . $conditions;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditions->getParameters());
                $languageItemIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

                $objectAction = new LanguageItemAction($languageItemIDs, 'delete');
                $objectAction->executeAction();
            }
        }

        // call category types
        foreach ($this->getObjects() as $categoryEditor) {
            $categoryEditor->getProcessor()->afterDeletion($categoryEditor);
        }

        return $returnValue;
    }

    /**
     * @inheritDoc
     */
    public function toggleContainer()
    {
        $collapsibleObjectTypeName = $this->getObjects()[0]->getProcessor()->getObjectTypeName('com.woltlab.wcf.collapsibleContent');
        if ($collapsibleObjectTypeName === null) {
            throw new SystemException("Categories of this type don't support collapsing");
        }

        $objectTypeID = UserCollapsibleContentHandler::getInstance()->getObjectTypeID($collapsibleObjectTypeName);
        $collapsedCategories = UserCollapsibleContentHandler::getInstance()->getCollapsedContent($objectTypeID);

        $categoryID = $this->objects[0]->categoryID;
        if (\array_search($categoryID, $collapsedCategories) !== false) {
            UserCollapsibleContentHandler::getInstance()->markAsOpened($objectTypeID, $categoryID);
        } else {
            UserCollapsibleContentHandler::getInstance()->markAsCollapsed($objectTypeID, $categoryID);
        }
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        // check if showOrder needs to be recalculated
        if (\count($this->objects) == 1 && isset($this->parameters['data']['parentCategoryID']) && isset($this->parameters['data']['showOrder'])) {
            $categoryEditor = $this->getObjects()[0];
            if ($categoryEditor->parentCategoryID != $this->parameters['data']['parentCategoryID'] || $categoryEditor->showOrder != $this->parameters['data']['showOrder']) {
                $this->parameters['data']['showOrder'] = $categoryEditor->updateShowOrder(
                    $this->parameters['data']['parentCategoryID'],
                    $this->parameters['data']['showOrder']
                );
            }
        }

        parent::update();

        if (isset($this->parameters['data']['parentCategoryID'])) {
            $objectType = null;
            $parentUpdates = [];

            foreach ($this->getObjects() as $category) {
                if ($objectType === null) {
                    $objectType = $category->getObjectType();
                }

                if ($category->parentCategoryID != $this->parameters['data']['parentCategoryID']) {
                    $parentUpdates[$category->categoryID] = [
                        'oldParentCategoryID' => $category->parentCategoryID,
                        'newParentCategoryID' => $this->parameters['data']['parentCategoryID'],
                    ];
                }
            }

            if (!empty($parentUpdates)) {
                $objectType->getProcessor()->changedParentCategories($parentUpdates);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function updatePosition()
    {
        $objectType = null;
        $parentUpdates = [];

        WCF::getDB()->beginTransaction();
        foreach ($this->parameters['data']['structure'] as $parentCategoryID => $categoryIDs) {
            $showOrder = 1;
            foreach ($categoryIDs as $categoryID) {
                $category = CategoryHandler::getInstance()->getCategory($categoryID);
                if ($objectType === null) {
                    $objectType = $category->getObjectType();
                }

                if ($category->parentCategoryID != $parentCategoryID) {
                    $parentUpdates[$categoryID] = [
                        'oldParentCategoryID' => $category->parentCategoryID,
                        'newParentCategoryID' => $parentCategoryID,
                    ];
                }

                $this->objects[$categoryID]->update([
                    'parentCategoryID' => $parentCategoryID ? $this->objects[$parentCategoryID]->categoryID : 0,
                    'showOrder' => $showOrder++,
                ]);
            }
        }
        WCF::getDB()->commitTransaction();

        if (!empty($parentUpdates)) {
            $objectType->getProcessor()->changedParentCategories($parentUpdates);
        }
    }

    /**
     * @inheritDoc
     */
    public function validateCreate()
    {
        $this->readInteger('objectTypeID', false, 'data');

        $objectType = CategoryHandler::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
        if ($objectType === null) {
            throw new UserInputException('objectTypeID', 'invalid');
        }
        if (!$objectType->getProcessor()->canAddCategory()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        // read objects
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        foreach ($this->getObjects() as $categoryEditor) {
            if (!$categoryEditor->getProcessor()->canDeleteCategory()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validateToggleContainer()
    {
        $this->validateUpdate();
    }

    /**
     * @inheritDoc
     */
    public function validateUpdate()
    {
        // read objects
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        foreach ($this->getObjects() as $categoryEditor) {
            if (!$categoryEditor->getProcessor()->canEditCategory()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validateUpdatePosition()
    {
        // validate 'structure' parameter
        if (!isset($this->parameters['data']['structure']) || !\is_array($this->parameters['data']['structure'])) {
            throw new UserInputException('structure');
        }

        // validate given category ids
        foreach ($this->parameters['data']['structure'] as $parentCategoryID => $categoryIDs) {
            if ($parentCategoryID) {
                // validate category
                $category = CategoryHandler::getInstance()->getCategory($parentCategoryID);
                if ($category === null) {
                    throw new UserInputException('structure');
                }

                // validate permissions
                if (!$category->getProcessor()->canEditCategory()) {
                    throw new PermissionDeniedException();
                }

                $this->objects[$category->categoryID] = new $this->className($category);
            }

            foreach ($categoryIDs as $categoryID) {
                // validate category
                $category = CategoryHandler::getInstance()->getCategory($categoryID);
                if ($category === null) {
                    throw new UserInputException('structure');
                }

                // validate permissions
                if (!$category->getProcessor()->canEditCategory()) {
                    throw new PermissionDeniedException();
                }

                $this->objects[$category->categoryID] = new $this->className($category);
            }
        }
    }
}
