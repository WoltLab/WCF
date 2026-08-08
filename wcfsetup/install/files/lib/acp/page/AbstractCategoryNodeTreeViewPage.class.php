<?php

namespace wcf\acp\page;

use wcf\data\object\type\ObjectType;
use wcf\page\AbstractNodeTreeViewPage;
use wcf\system\category\CategoryHandler;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\nodeTreeView\admin\CategoryNodeTreeView;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Abstract implementation of a page that lists all categories of a certain object type using a node tree view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractNodeTreeViewPage<CategoryNodeTreeView>
 */
abstract class AbstractCategoryNodeTreeViewPage extends AbstractNodeTreeViewPage
{
    /**
     * language item with the page title
     */
    public string $pageTitle = 'wcf.category.list';

    /**
     * category object type object
     */
    public ?ObjectType $objectType = null;

    /**
     * name of the category object type
     */
    public string $objectTypeName = '';

    /**
     * @inheritDoc
     */
    public $templateName = 'categoryNodeTreeView';

    #[\Override]
    public function readData()
    {
        $this->objectType = CategoryHandler::getInstance()->getObjectTypeByName($this->objectTypeName);
        if ($this->objectType === null) {
            throw new InvalidObjectTypeException($this->objectTypeName, 'com.woltlab.wcf.category');
        }

        parent::readData();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'objectType' => $this->objectType,
            'addFormLink' => $this->objectType->getProcessor()->getAddFormLink(),
        ]);

        if ($this->pageTitle !== '') {
            WCF::getTPL()->assign('pageTitle', $this->pageTitle);
        }
    }

    #[\Override]
    protected function createNodeTreeView(): CategoryNodeTreeView
    {
        return new CategoryNodeTreeView($this->objectTypeName);
    }
}
