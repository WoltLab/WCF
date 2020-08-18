<?php
namespace wcf\system\worker;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\user\content\provider\IUserContentProvider;
use wcf\system\WCF;

/**
 * Worker implementation for updating users.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 * @since	5.2
 */
class UserContentRemoveWorker extends AbstractWorker {
	/**
	 * variable name for the session to store the data
	 */
	const USER_CONTENT_REMOVE_WORKER_SESSION_NAME = 'userContentRemoveWorkerData';
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 10;
	
	/**
	 * user
	 * @var	User
	 */
	protected $user = null;
	
	/**
	 * data
	 * @var mixed 
	 */
	protected $data = null;
	
	/**
	 * 
	 * @var null 
	 */
	public $contentProvider = null;
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if (!isset($this->parameters['userID'])) {
			throw new \InvalidArgumentException('userID missing');
		}
		
		$this->user = new User($this->parameters['userID']);
		
		if (!$this->user->userID) {
			throw new \InvalidArgumentException('userID is unknown.');
		}
		
		if (!$this->user->canEdit()) {
			throw new PermissionDeniedException();
		}
		
		if (isset($this->parameters['contentProvider'])) {
			if (!is_array($this->parameters['contentProvider'])) {
				throw new \InvalidArgumentException('The parameter `contentProvider` must be an array.');
			}
			
			$knownContentProvider = array_map(function ($contentProvider) {
				return $contentProvider->objectType;
			}, ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.content.userContentProvider'));
			
			$unknownContentProvider = array_diff($this->parameters['contentProvider'], $knownContentProvider);
			if (!empty($unknownContentProvider)) {
				throw new \InvalidArgumentException('The parameter `contentProvider` contains unknown objectTypes ('. implode(', ', $unknownContentProvider) .').');
			}
			
			$this->contentProvider = $this->parameters['contentProvider'];
		}
		
		if ($this->loopCount === 0) {
			$this->generateData();
		}
		else {
			$data = WCF::getSession()->getVar(self::USER_CONTENT_REMOVE_WORKER_SESSION_NAME);
			
			if (!is_array($data) || !isset($data[$this->user->userID])) {
				throw new \RuntimeException('`data` variable in session is invalid or missing.');
			}
			
			$this->data = $data[$this->user->userID];
		}
	}
	
	/**
	 * Generate the data variable. 
	 */
	private function generateData() {
		$this->data = [
			'provider' => [],
			'count' => 0
		];
		
		$contentProviders = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.content.userContentProvider');
		
		// add the required object types for the select content provider
		if (is_array($this->contentProvider)) {
			foreach ($this->contentProvider as $contentProvider) {
				$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.content.userContentProvider', $contentProvider);
				
				if ($objectType->requiredobjecttype !== null) {
					$objectTypeNames = explode(',', $objectType->requiredobjecttype);
					
					foreach ($objectTypeNames as $objectTypeName) {
						$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.content.userContentProvider', $objectTypeName);
						
						if ($objectType === null) {
							throw new \RuntimeException('Unknown required object type "' . $objectTypeName . '" for object type "' . $contentProvider . '" given.');
						}
						
						$this->contentProvider[] = $objectTypeName;
					}
				}
			}
		}
		
		foreach ($contentProviders as $contentProvider) {
			if ($this->contentProvider === null || (is_array($this->contentProvider) && in_array($contentProvider->objectType, $this->contentProvider))) {
				/** @var IUserContentProvider $processor */
				$processor = $contentProvider->getProcessor();
				$contentList = $processor->getContentListForUser($this->user);
				$count = $contentList->countObjects();
				
				if ($count) {
					$this->data['provider'][$contentProvider->objectType] = [
						'count' => $count,
						'objectTypeID' => $contentProvider->objectTypeID,
						'nicevalue' => $contentProvider->nicevalue ?: 0
					];
					
					$this->data['count'] += ceil($count / $this->limit) * $this->limit;
				}
			}
		}
		
		// sort object types
		uasort($this->data['provider'], function ($a, $b) {
			$niceValueA = ($a['nicevalue'] ?: 0);
			$niceValueB = ($b['nicevalue'] ?: 0);
			
			return $niceValueA <=> $niceValueB;
		});
	}
	
	/**
	 * @inheritDoc
	 */
	protected function countObjects() {
		$this->count = $this->data['count'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		if (empty($this->data['provider'])) {
			return;
		}
		
		$values = array_keys($this->data['provider']);
		$providerObjectType = array_shift($values);
		
		/** @var IUserContentProvider $processor */
		$processor = ObjectTypeCache::getInstance()->getObjectType($this->data['provider'][$providerObjectType]['objectTypeID'])->getProcessor();
		
		$objectList = $processor->getContentListForUser($this->user);
		$objectList->sqlLimit = $this->limit;
		$objectList->readObjectIDs();
		$processor->deleteContent($objectList->objectIDs);
		
		$this->data['provider'][$providerObjectType]['count'] -= $this->limit;
		
		if ($this->data['provider'][$providerObjectType]['count'] <= 0) {
			unset($this->data['provider'][$providerObjectType]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function finalize() {
		parent::finalize();
		
		$dataArray = WCF::getSession()->getVar(self::USER_CONTENT_REMOVE_WORKER_SESSION_NAME);
		
		if (!is_array($dataArray)) {
			$dataArray = [];
		}
		
		$dataArray[$this->user->userID] = $this->data;
		
		WCF::getSession()->register(self::USER_CONTENT_REMOVE_WORKER_SESSION_NAME, $dataArray);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getProceedURL() {
		return LinkHandler::getInstance()->getLink('UserList');
	}
}
