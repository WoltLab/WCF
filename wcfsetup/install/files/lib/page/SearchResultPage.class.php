<?php
namespace wcf\page;
use wcf\data\search\Search;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\search\SearchEngine;
use wcf\system\WCF;

/**
 * Shows the result of a search request.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.search
 * @subpackage	page
 * @category	Community Framework
 */
class SearchResultPage extends MultipleLinkPage {
	/**
	 * @see	wcf\page\MultipleLinkPage::$itemsPerPage
	 */
	public $itemsPerPage = SEARCH_RESULTS_PER_PAGE;
	
	/**
	 * highlight string
	 * @var	string
	 */
	public $highlight = '';
	
	/**
	 * search id
	 * @var	integer
	 */
	public $searchID = 0;
	
	/**
	 * search object
	 * @var	wcf\data\search\Search
	 */
	public $search = null;
	
	/**
	 * messages
	 * @var	array
	 */
	public $messages = array();
	
	/**
	 * search data
	 * @var	array
	 */
	public $searchData = null;
	
	/**
	 *  result list template
	 * @var string
	 */
	public $resultListTemplateName = 'searchResultList';
	
	/**
	 * result list template's application
	 * @var string
	 */
	public $resultListApplication = 'wcf';
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['highlight'])) $this->highlight = $_REQUEST['highlight'];
		if (isset($_REQUEST['id'])) $this->searchID = intval($_REQUEST['id']);
		$this->search = new Search($this->searchID);
		if (!$this->search->searchID || $this->search->searchType != 'messages') {
			throw new IllegalLinkException();
		}
		if ($this->search->userID && $this->search->userID != WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
		
		// get search data
		$this->searchData = unserialize($this->search->searchData);
		
		// check package id of this search
		if (!empty($this->searchData['packageID']) && $this->searchData['packageID'] != PACKAGE_ID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// cache message data
		$this->cacheMessageData();
		
		// get messages
		$this->readMessages();
	}
	
	/**
	 * Caches the message data.
	 */
	protected function cacheMessageData() {
		$types = array();
		
		// group object id by object type
		for ($i = $this->startIndex - 1; $i < $this->endIndex; $i++) {
			$type = $this->searchData['results'][$i]['objectType'];
			$objectID = $this->searchData['results'][$i]['objectID'];
			
			if (!isset($types[$type])) $types[$type] = array();
			$types[$type][] = $objectID;
		}
		
		foreach ($types as $type => $objectIDs) {
			$objectType = SearchEngine::getInstance()->getObjectType($type);
			$objectType->cacheObjects($objectIDs, (isset($this->searchData['additionalData'][$type]) ? $this->searchData['additionalData'][$type] : null));
		}
	}
	
	/**
	 * Gets the data of the messages.
	 */
	protected function readMessages() {
		for ($i = $this->startIndex - 1; $i < $this->endIndex; $i++) {
			$type = $this->searchData['results'][$i]['objectType'];
			$objectID = $this->searchData['results'][$i]['objectID'];
			
			$objectType = SearchEngine::getInstance()->getObjectType($type);
			if (($message = $objectType->getObject($objectID)) !== null) {
				$this->messages[] = $message;
			}
		}
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'query' => $this->searchData['query'],
			'objects' => $this->messages,
			'searchData' => $this->searchData,
			'searchID' => $this->searchID,
			'highlight' => $this->highlight,
			'sortField' => $this->searchData['sortField'],
			'sortOrder' => $this->searchData['sortOrder'],
			'alterable' => (!empty($this->searchData['alterable']) ? 1 : 0),
			'objectTypes' => SearchEngine::getInstance()->getAvailableObjectTypes(),
			'resultListTemplateName' => $this->resultListTemplateName,
			'resultListApplication' => $this->resultListApplication
		));
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::countItems()
	 */
	public function countItems() {
		// call countItems event
		EventHandler::getInstance()->fireAction($this, 'countItems');
		
		return count($this->searchData['results']);
	}
	
	/**
	 * @see	wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() { }
	
	/**
	 * @see	wcf\page\MultipleLinkPage::readObjects()
	 */
	protected function readObjects() { }
}
