<?php
namespace wcf\page;
use wcf\data\DatabaseObjectList;
use wcf\system\event\EventHandler;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Provides default implementations for a multiple link page.
 * Handles the page number parameter automatically.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
abstract class MultipleLinkPage extends AbstractPage {
	/**
	 * current page number
	 * @var	integer
	 */
	public $pageNo = 0;
	
	/**
	 * number of all pages
	 * @var	integer
	 */
	public $pages = 0;
	
	/**
	 * number of items shown per page
	 * @var	integer
	 */
	public $itemsPerPage = 20;
	
	/**
	 * number of all items
	 * @var	integer
	 */
	public $items = 0;
	
	/**
	 * indicates the range of the listed items
	 * @var	integer
	 */
	public $startIndex = 0;
	
	/**
	 * indicates the range of the listed items.
	 * @var	integer
	 */
	public $endIndex = 0;
	
	/**
	 * DatabaseObjectList object
	 * @var	\wcf\data\DatabaseObjectList
	 */
	public $objectList = null;
	
	/**
	 * class name for DatabaseObjectList
	 * @var	string
	 */
	public $objectListClassName = '';
	
	/**
	 * selected sort field
	 * @var	string
	 */
	public $sortField = '';
	
	/**
	 * selected sort order
	 * @var	string
	 */
	public $sortOrder = '';
	
	/**
	 * @inheritDoc
	 */
	public $sqlLimit = 0;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOffset = '';
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// read page number parameter
		if (isset($_REQUEST['pageNo'])) $this->pageNo = intval($_REQUEST['pageNo']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// initialize database object list
		$this->initObjectList();
		
		// calculates page number
		$this->calculateNumberOfPages();
		
		// read objects
		if ($this->items) {
			$this->sqlLimit = $this->itemsPerPage;
			$this->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
			if ($this->sortField && $this->sortOrder) {
				if ($this->objectList !== null) {
					$alias = $this->objectList->getDatabaseTableAlias();
					$this->sqlOrderBy = $this->sortField." ".$this->sortOrder.", ".($alias ? $alias."." : "").$this->objectList->getDatabaseTableIndexName()." ".$this->sortOrder;
				}
				else {
					$this->sqlOrderBy = $this->sortField." ".$this->sortOrder;
				}
			}
			$this->readObjects();
		}
	}
	
	/**
	 * Initializes DatabaseObjectList instance.
	 */
	protected function initObjectList() {
		if (empty($this->objectListClassName)) {
			throw new SystemException('DatabaseObjectList class name not specified.');
		}
		
		if (!is_subclass_of($this->objectListClassName, DatabaseObjectList::class)) {
			throw new ParentClassException($this->objectListClassName, DatabaseObjectList::class);
		}
		
		$this->objectList = new $this->objectListClassName();
	}
	
	/**
	 * Reads object list.
	 */
	protected function readObjects() {
		$this->objectList->sqlLimit = $this->sqlLimit;
		$this->objectList->sqlOffset = $this->sqlOffset;
		if ($this->sqlOrderBy) $this->objectList->sqlOrderBy = $this->sqlOrderBy;
		
		EventHandler::getInstance()->fireAction($this, 'beforeReadObjects');
		
		$this->objectList->readObjects();
	}
	
	/**
	 * Calculates the number of pages and
	 * handles the given page number parameter.
	 */
	public function calculateNumberOfPages() {
		// call calculateNumberOfPages event
		EventHandler::getInstance()->fireAction($this, 'calculateNumberOfPages');
		
		// calculate number of pages
		$this->items = $this->countItems();
		$this->pages = intval(ceil($this->items / $this->itemsPerPage));
		
		// correct active page number
		if ($this->pageNo > $this->pages) $this->pageNo = $this->pages;
		if ($this->pageNo < 1) $this->pageNo = 1;
		
		// calculate start and end index
		$this->startIndex = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->endIndex = $this->startIndex + $this->itemsPerPage;
		$this->startIndex++;
		if ($this->endIndex > $this->items) $this->endIndex = $this->items;
	}
	
	/**
	 * Counts the displayed items.
	 * 
	 * @return	integer
	 */
	public function countItems() {
		// call countItems event
		EventHandler::getInstance()->fireAction($this, 'countItems');
		
		return $this->objectList->countObjects();
	}
	
	/**
	 * Returns true if current page is the first page.
	 * 
	 * @return	boolean
	 */
	public function isFirstPage() {
		return ($this->pageNo == 1);
	}
	
	/**
	 * Returns true if current page is the last page.
	 * 
	 * @return	boolean
	 */
	public function isLastPage() {
		return ($this->items == $this->endIndex);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign page parameters
		WCF::getTPL()->assign([
			'pageNo' => $this->pageNo,
			'pages' => $this->pages,
			'items' => $this->items,
			'itemsPerPage' => $this->itemsPerPage,
			'startIndex' => $this->startIndex,
			'endIndex' => $this->endIndex,
			'objects' => $this->objectList
		]);
	}
}
