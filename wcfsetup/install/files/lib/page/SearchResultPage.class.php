<?php
namespace wcf\page;
use wcf\data\search\ICustomIconSearchResultObject;
use wcf\data\search\ISearchResultObject;
use wcf\data\search\Search;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\ImplementationException;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchEngine;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the result of a search request.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class SearchResultPage extends MultipleLinkPage {
	/**
	 * list of custom icons per message
	 * @var string[]
	 */
	public $customIcons = [];
	
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
	 * @var	Search
	 */
	public $search;
	
	/**
	 * messages
	 * @var	array
	 */
	public $messages = [];
	
	/**
	 * search data
	 * @var	array
	 */
	public $searchData;
	
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
			$this->redirectOrReject();
			
		}
		if ($this->search->userID && $this->search->userID != WCF::getUser()->userID) {
			$this->redirectOrReject();
		}
		
		// get search data
		$this->searchData = unserialize($this->search->searchData);
	}
	
	/**
	 * Attempts to start a new search if the search id is invalid or unavailable, and the
	 * highlight parameter is available.
	 */
	protected function redirectOrReject() {
		if (!empty($this->highlight)) {
			HeaderUtil::redirect(
				LinkHandler::getInstance()->getLink('Search', ['q' => $this->highlight])
			);
			exit;
		}
		else {
			throw new IllegalLinkException();
		}
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
				
				$customIcon = '';
				if ($message instanceof ICustomIconSearchResultObject) {
					$customIcon = $message->getCustomSearchResultIcon();
				}
				
				$this->messages[] = $message;
				$this->customIcons[spl_object_hash($message)] = $customIcon;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$searchPreselectObjectType = 'everywhere';
		if (count($this->searchData['selectedObjectTypes']) === 1) $searchPreselectObjectType = reset($this->searchData['selectedObjectTypes']);
		
		WCF::getTPL()->assign([
			'query' => $this->searchData['query'],
			'objects' => $this->messages,
			'searchData' => $this->searchData,
			'searchID' => $this->searchID,
			'highlight' => $this->highlight,
			'sortField' => $this->searchData['sortField'],
			'sortOrder' => $this->searchData['sortOrder'],
			'alterable' => !empty($this->searchData['alterable']) ? 1 : 0,
			'objectTypes' => SearchEngine::getInstance()->getAvailableObjectTypes(),
			'resultListTemplateName' => $this->resultListTemplateName,
			'resultListApplication' => $this->resultListApplication,
			'application' => ApplicationHandler::getInstance()->getAbbreviation(ApplicationHandler::getInstance()->getActiveApplication()->packageID),
			'searchPreselectObjectType' => $searchPreselectObjectType,
			'customIcons' => $this->customIcons
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
