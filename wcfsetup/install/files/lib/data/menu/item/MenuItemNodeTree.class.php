<?php

namespace wcf\data\menu\item;

use wcf\system\page\PageLocationManager;
use wcf\system\request\RequestHandler;
use wcf\system\request\RouteHandler;
use wcf\util\Url;

/**
 * Represents a menu item node tree.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class MenuItemNodeTree
{
    /**
     * if `false`, individual menu item visibility will not be checked
     * @var bool
     */
    public $checkVisibility;

    /**
     * menu id
     * @var int
     */
    public $menuID;

    /**
     * list of menu items
     * @var MenuItem[]
     */
    public $menuItems = [];

    /**
     * menu item structure
     * @var mixed[]
     */
    public $menuItemStructure = [];

    /**
     * root node
     * @var MenuItemNode
     */
    public $node;

    /**
     * number of visible items
     * @var int
     */
    protected $visibleItemCount = 0;

    /**
     * Creates a new MenuItemNodeTree object.
     *
     * @param int $menuID menu id
     * @param MenuItemList $menuItemList optional object to be provided when building the tree from cache
     * @param bool $checkVisibility if `false`, individual menu item visibility will not be checked
     */
    public function __construct($menuID, ?MenuItemList $menuItemList = null, $checkVisibility = true)
    {
        $this->menuID = $menuID;
        $this->checkVisibility = $checkVisibility;

        // load menu items
        if ($menuItemList === null) {
            $menuItemList = new MenuItemList();
            $menuItemList->getConditionBuilder()->add('menu_item.menuID = ?', [$this->menuID]);
            $menuItemList->sqlOrderBy = "menu_item.showOrder";
            $menuItemList->readObjects();
        }

        // find possible active menu items
        $activeMenuItems = [];

        if (!RequestHandler::getInstance()->isACPRequest()) {
            $requestParameters = Url::parseQueryString($_SERVER['QUERY_STRING']);

            $possibleLocations = PageLocationManager::getInstance()->getLocations();
            for ($i = 0, $length = \count($possibleLocations); $i < $length; $i++) {
                foreach ($menuItemList->getObjects() as $menuItem) {
                    if ($menuItem->pageID !== $possibleLocations[$i]['pageID']) {
                        continue;
                    }

                    if ($menuItem->pageObjectID !== $possibleLocations[$i]['pageObjectID']) {
                        continue;
                    }

                    if ($menuItem->urlParameters !== '') {
                        $expectedParameters = Url::parseQueryString($menuItem->urlParameters);
                        if (\array_diff($expectedParameters, $requestParameters) !== []) {
                            continue;
                        }
                    }

                    $activeMenuItems[$i] ??= [];
                    $activeMenuItems[$i][] = $menuItem->itemID;
                }
            }
        }

        // build menu structure
        foreach ($menuItemList->getObjects() as $menuItem) {
            $menuItem->cachePageObject();

            $this->menuItems[$menuItem->itemID] = $menuItem;

            $parentItemID = $menuItem->parentItemID ?? '';
            $this->menuItemStructure[$parentItemID] ??= [];
            $this->menuItemStructure[$parentItemID][] = $menuItem->itemID;
        }

        // generate node tree
        $this->node = new MenuItemNode();
        $this->node->setChildren($this->generateNodeTree(null, $this->node));

        // mark nodes as active
        if (!empty($activeMenuItems)) {
            $nodeList = $this->getNodeList();
            foreach ($activeMenuItems as $itemIDs) {
                for ($i = 0, $length = \count($itemIDs); $i < $length; $i++) {
                    /** @var MenuItemNode $node */
                    foreach ($nodeList as $node) {
                        if ($node->itemID == $itemIDs[$i]) {
                            $node->setIsActive();

                            // only one effective item can be marked as active, use the first
                            // occurrence with the highest priority and ignore everything else
                            return;
                        }
                    }
                }
            }
        }
    }

    /**
     * Generates the node tree recursively.
     *
     * @param int $parentID parent menu item id
     * @param MenuItemNode $parentNode parent menu item object
     * @return  MenuItemNode[]      nested menu item tree
     */
    protected function generateNodeTree($parentID = null, ?MenuItemNode $parentNode = null)
    {
        $nodes = [];

        $itemIDs = ($this->menuItemStructure[$parentID ?? ''] ?? []);
        foreach ($itemIDs as $itemID) {
            $menuItem = $this->menuItems[$itemID];

            if ($this->checkVisibility && !$menuItem->isVisible()) {
                continue;
            }

            $node = new MenuItemNode(
                $parentNode,
                $menuItem,
                ($parentNode !== null ? ($parentNode->getDepth() + 1) : 0)
            );
            $nodes[] = $node;

            // get children
            $node->setChildren($this->generateNodeTree($itemID, $node));

            // increase item counter
            $this->visibleItemCount++;
        }

        return $nodes;
    }

    /**
     * Returns the menu item node tree.
     *
     * @return  MenuItemNode[]
     */
    public function getNodeTree()
    {
        // @phpstan-ignore return.type
        return $this->node->getChildren();
    }

    /**
     * Returns the iterable node list.
     *
     * @return \RecursiveIteratorIterator<MenuItemNode>
     */
    public function getNodeList()
    {
        return new \RecursiveIteratorIterator($this->node, \RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * Returns the number of visible items.
     *
     * @return  int
     */
    public function getVisibleItemCount()
    {
        return $this->visibleItemCount;
    }
}
