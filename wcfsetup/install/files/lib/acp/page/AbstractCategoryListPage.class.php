<?php

namespace wcf\acp\page;

use wcf\data\category\CategoryNodeTree;
use wcf\data\object\type\ObjectType;
use wcf\page\AbstractPage;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Abstract implementation of a page with lists all categories of a certain object
 * type.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `AbstractCategoryNodeTreeViewPage` instead.
 */
abstract class AbstractCategoryListPage extends AbstractPage
{
    /**
     * name of the controller used to add new categories
     * @var string
     */
    public $addController = '';

    /**
     * category node tree
     * @var ?CategoryNodeTree
     */
    public $categoryNodeTree;

    /**
     * name of the controller used to edit categories
     * @var string
     */
    public $editController = '';

    /**
     * language item with the page title
     * @var string
     */
    public $pageTitle = 'wcf.category.list';

    /**
     * category object type object
     * @var ?ObjectType
     */
    public $objectType;

    /**
     * name of the category object type
     * @var string
     */
    public $objectTypeName = '';

    /**
     * @inheritDoc
     */
    public $templateName = 'categoryList';

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function __run()
    {
        $classNameParts = \explode('\\', static::class);
        $className = \array_pop($classNameParts);

        // autoset controllers
        if (empty($this->addController)) {
            $this->addController = \str_replace('ListPage', 'Add', $className);
        }
        if (empty($this->editController)) {
            $this->editController = \str_replace('ListPage', 'Edit', $className);
        }

        return parent::__run();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'addController' => $this->addController,
            'categoryNodeList' => $this->categoryNodeTree->getIterator(),
            'editController' => $this->editController,
            'objectType' => $this->objectType,
        ]);

        if ($this->pageTitle !== '') {
            WCF::getTPL()->assign('pageTitle', $this->pageTitle);
        }
    }

    /**
     * Checks if the active user has the needed permissions to view this list.
     *
     * @return void
     */
    protected function checkCategoryPermissions()
    {
        if (!$this->objectType->getProcessor()->canDeleteCategory() && !$this->objectType->getProcessor()->canEditCategory()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Reads the categories.
     *
     * @return void
     */
    protected function readCategories()
    {
        $this->categoryNodeTree = new CategoryNodeTree($this->objectType->objectType, 0, true);
    }

    #[\Override]
    public function readData()
    {
        $this->objectType = CategoryHandler::getInstance()->getObjectTypeByName($this->objectTypeName);
        if ($this->objectType === null) {
            throw new InvalidObjectTypeException($this->objectTypeName, 'com.woltlab.wcf.category');
        }

        // check permissions
        $this->checkCategoryPermissions();

        $this->readCategories();

        parent::readData();
    }
}
