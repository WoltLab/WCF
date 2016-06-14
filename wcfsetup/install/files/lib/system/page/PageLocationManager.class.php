<?php
namespace wcf\system\page;
use wcf\data\page\PageCache;
use wcf\data\ITitledLinkObject;
use wcf\system\exception\SystemException;
use wcf\system\request\RequestHandler;
use wcf\system\SingletonFactory;

/**
 * Handles page locations for use with menu active markings.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page
 * @since	3.0
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
		if ($activeRequest === null) {
			return;
		}
		
		$metaData = $activeRequest->getMetaData();
		$page = null;
		if (isset($metaData['cms'])) {
			$pageID = $metaData['cms']['pageID'];
			
			$page = PageCache::getInstance()->getPage($pageID);
		}
		else {
			$page = PageCache::getInstance()->getPageByController($activeRequest->getClassName());
			if ($page !== null) {
				$pageID = $page->pageID;
				
				if (!empty($_REQUEST['id'])) $pageObjectID = intval($_REQUEST['id']);
			}
		}
		
		if ($page !== null) {
			$this->stack[] = [
				'identifier' => $page->identifier,
				'link' => $page->getLink(),
				'pageID' => $pageID,
				'pageObjectID' => $pageObjectID,
				'title' => $page->getTitle()
			];
		}
	}
	
	/**
	 * Appends a parent location to the stack, the later it is added the lower
	 * is its assumed priority when matching suitable menu items.
	 * 
	 * @param	string			$identifier		internal page identifier
	 * @param	integer			$pageObjectID		page object id
	 * @param	ITitledLinkObject	$locationObject		optional label for breadcrumbs usage
	 * @param       boolean                 $useAsParentLocation
	 * @throws	SystemException
	 */
	public function addParentLocation($identifier, $pageObjectID = 0, ITitledLinkObject $locationObject = null, $useAsParentLocation = false) {
		$page = PageCache::getInstance()->getPageByIdentifier($identifier);
		if ($page === null) {
			throw new SystemException("Unknown page identifier '".$identifier."'.");
		}
		
		// check if the provided location is already part of the stack
		for ($i = 0, $length = count($this->stack); $i < $length; $i++) {
			if ($this->stack[$i]['identifier'] == $identifier && $this->stack[$i]['pageObjectID'] == $pageObjectID) {
				return;
			}
		}
		
		if ($locationObject !== null) {
			$link = $locationObject->getLink();
			$title = $locationObject->getTitle();
		}
		else {
			$link = $page->getLink();
			$title = $page->getTitle();
		}
		
		$this->stack[] = [
			'identifier' => $identifier,
			'link' => $link,
			'pageID' => $page->pageID,
			'pageObjectID' => $pageObjectID,
			'title' => $title,
			'useAsParentLocation' => $useAsParentLocation
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
