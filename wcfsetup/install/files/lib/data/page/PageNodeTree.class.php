<?php

namespace wcf\data\page;

/**
 * Represents a page node tree.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Page
 * @since   3.0
 */
class PageNodeTree
{
    /**
     * parent page id
     * @var int
     */
    public $parentID;

    /**
     * start depth
     * @var int
     */
    public $startDepth = 0;

    /**
     * list of pages
     * @var Page[]
     */
    public $pages = [];

    /**
     * page structure
     * @var mixed[]
     */
    public $pageStructure = [];

    /**
     * root node
     * @var PageNode
     */
    public $node;

    /**
     * Creates a new PageNodeTree object.
     *
     * @param   int         $parentID
     * @param   int         $startDepth
     */
    public function __construct($parentID = null, $startDepth = 0)
    {
        $this->parentID = $parentID;
        $this->startDepth = $startDepth;

        // load pages
        $pageList = new PageList();
        $pageList->sqlOrderBy = "page.name";
        $pageList->readObjects();

        foreach ($pageList as $page) {
            $this->pages[$page->pageID] = $page;

            if (!isset($this->pageStructure[$page->parentPageID])) {
                $this->pageStructure[$page->parentPageID] = [];
            }
            $this->pageStructure[$page->parentPageID][] = $page->pageID;
        }

        // generate node tree
        $this->node = new PageNode(null, null, $startDepth);
        $this->node->setChildren($this->generateNodeTree($parentID, $this->node));
    }

    /**
     * Generates the node tree recursively.
     *
     * @param   int         $parentID
     * @param   PageNode        $parentNode
     * @return  PageNode[]
     */
    protected function generateNodeTree($parentID, ?PageNode $parentNode = null)
    {
        $nodes = [];

        $pageIDs = ($this->pageStructure[$parentID] ?? []);
        foreach ($pageIDs as $pageID) {
            $page = $this->pages[$pageID];
            $node = new PageNode($parentNode, $page, ($parentNode !== null ? ($parentNode->getDepth() + 1) : 0));
            $nodes[] = $node;

            // get children
            $node->setChildren($this->generateNodeTree($pageID, $node));
        }

        return $nodes;
    }

    /**
     * Returns the page node tree.
     *
     * @return  PageNode[]
     */
    public function getNodeTree()
    {
        return $this->node->getChildren();
    }

    /**
     * Returns the iterable node list.
     *
     * @return  \RecursiveIteratorIterator
     */
    public function getNodeList()
    {
        return new \RecursiveIteratorIterator($this->node, \RecursiveIteratorIterator::SELF_FIRST);
    }
}
