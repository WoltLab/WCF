<?php
namespace wcf\system\page;
use wcf\data\page\PageCache;
use wcf\system\exception\SystemException;
use wcf\system\request\RequestHandler;
use wcf\system\SingletonFactory;

/**
 * Handles page locations for use with menu active markings.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.page
 * @category	Community Framework
 */
class PageLocationManager extends SingletonFactory {
	/**
	 * list of locations with descending priority
	 * @var	array
	 */
	protected $stack = [];
	
	/**
	 * @inheritDoc
	 */
	public function init() {
		$pageID = $pageObjectID = 0;
		
		$activeRequest = RequestHandler::getInstance()->getActiveRequest();
		$metaData = $activeRequest->getMetaData();
		if (isset($metaData['cms'])) {
			$pageID = $metaData['cms']['pageID'];
		}
		else {
			$page = PageCache::getInstance()->getPageByController($activeRequest->getClassName());
			if ($page !== null) {
				$pageID = $page->pageID;
				
				if (!empty($_REQUEST['id'])) $pageObjectID = intval($_REQUEST['id']);
			}
		}
		
		if ($pageID !== null) {
			$this->stack[] = [
				'pageID' => $pageID,
				'pageObjectID' => $pageObjectID
			];
		}
	}
	
	/**
	 * Appends a parent location to the stack, the later it is added the lower
	 * is its assumed priority when matching suitable menu items.
	 * 
	 * @param	string		$identifier	internal page identifier
	 * @param	integer		$pageObjectID	page object id
	 * @throws	SystemException
	 */
	public function addParentLocation($identifier, $pageObjectID = 0) {
		$page = PageCache::getInstance()->getPageByIdentifier($identifier);
		if ($page === null) {
			throw new SystemException("Unknown page identifier '".$identifier."'.");
		}
		
		$this->stack[] = [
			'pageID' => $page->pageID,
			'pageObjectID' => $pageObjectID
		];
	}
	
	/**
	 * Returns the list of locations with descending priority.
	 * 
	 * @return	array
	 */
	public function getLocations() {
		return $this->stack;
	}
}
