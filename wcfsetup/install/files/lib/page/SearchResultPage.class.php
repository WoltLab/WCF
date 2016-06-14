<?php
namespace wcf\page;
use wcf\data\search\ISearchResultObject;
use wcf\data\search\Search;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\ImplementationException;
use wcf\system\page\PageLocationManager;
use wcf\system\search\SearchEngine;
use wcf\system\WCF;

/**
 * Shows the result of a search request.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class SearchResultPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
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
	 * @var	\wcf\data\search\Search
	 */
	public $search = null;
	
	/**
	 * messages
	 * @var	array
	 */
	public $messages = [];
	
	/**
	 * search data
	 * @var	array
	 */
	public $searchData = null;
	
	/**
	 * result list template
	 * @var	string
	 */
	public $resultListTemplateName = 'searchResultList';
	
	/**
	 * result list template's application
	 * @var	string
	 */
	public $resultListApplication = 'wcf';
	
	/**
	 * @inheritDoc
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
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// cache message data
		$this->cacheMessageData();
		
		// get messages
		$this->readMessages();
		
		// set active menu item
		if (isset($this->searchData['selectedObjectTypes']) && count($this->searchData['selectedObjectTypes']) == 1) {
			$objectType = SearchEngine::getInstance()->getObjectType(reset($this->searchData['selectedObjectTypes']));
			$objectType->setLocation();
		}
		
		// add breadcrumbs
		PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.Search');
	}
	
	/**
	 * Caches the message data.
	 */
	protected function cacheMessageData() {
		$types = [];
		
		// group object id by object type
		for ($i = $this->startIndex - 1; $i < $this->endIndex; $i++) {
			$type = $this->searchData['results'][$i]['objectType'];
			$objectID = $this->searchData['results'][$i]['objectID'];
			
			if (!isset($types[$type])) $types[$type] = [];
			$types[$type][] = $objectID;
		}
		
		foreach ($types as $type => $objectIDs) {
			$objectType = SearchEngine::getInstance()->getObjectType($type);
			$objectType->cacheObjects($objectIDs, (isset($this->searchData['additionalData'][$type]) ? $this->searchData['additionalData'][$type] : null));
		}
	}
	
	/**
	 * Reads the data of the search result messages.
	 */
	protected function readMessages() {
		for ($i = $this->startIndex - 1; $i < $this->endIndex; $i++) {
			$type = $this->searchData['results'][$i]['objectType'];
			$objectID = $this->searchData['results'][$i]['objectID'];
			
			$objectType = SearchEngine::getInstance()->getObjectType($type);
			if (($message = $objectType->getObject($objectID)) !== null) {
				if (!($message instanceof ISearchResultObject)) {
					throw new ImplementationException(get_class($message), ISearchResultObject::class);
				}
				
				$this->messages[] = $message;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
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
			'resultListApplication' => $this->resultListApplication,
			'application' => ApplicationHandler::getInstance()->getAbbreviation(ApplicationHandler::getInstance()->getActiveApplication()->packageID)
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function countItems() {
		// call countItems event
		EventHandler::getInstance()->fireAction($this, 'countItems');
		
		return count($this->searchData['results']);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() { }
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() { }
}
