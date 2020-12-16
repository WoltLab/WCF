<?php
namespace wcf\system\worker;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\clipboard\ClipboardHandler;
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
	 * @var	User[]
	 */
	protected $user = [];
	
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
		if (isset($this->parameters['userID']) && !isset($this->parameters['userIDs'])) {
			$this->parameters['userIDs'] = [$this->parameters['userID']];
		}
		
		if (isset($this->parameters['userIDs']) && is_array($this->parameters['userIDs']) && !empty($this->parameters['userIDs'])) {
			$userList = new UserList();
			$userList->setObjectIDs($this->parameters['userIDs']);
			$userList->readObjects();
			
			if ($userList->count() !== count($this->parameters['userIDs'])) {
				$diff = array_diff($this->parameters['userIDs'], array_map(function (User $user) {
					return $user->userID;
				}, $userList->getObjects()));
				
				throw new \InvalidArgumentException('The parameter `userIDs` contains unknown values ('. implode(', ', $diff) .').');
			}
			
			foreach ($userList as $user) {
				if (!$user->canEdit()) {
					throw new PermissionDeniedException();
				}
				
				$this->user[] = $user;
			}
		}
		
		if (empty($this->user)) {
			throw new \InvalidArgumentException('The parameter `userIDs` is empty.');
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
			
			if (!is_array($data) || !isset($data[$this->generateKey()])) {
				throw new \RuntimeException('`data` variable in session is invalid or missing.');
			}
			
			$this->data = $data[$this->generateKey()];
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
				foreach ($this->user as $user) {
					/** @var IUserContentProvider $processor */
					$processor = $contentProvider->getProcessor();
					$contentList = $processor->getContentListForUser($user);
					$count = $contentList->countObjects();
					
					if ($count) {
						if (!isset($this->data['provider'][$contentProvider->objectType])) {
							$this->data['provider'][$contentProvider->objectType] = [
								'count' => 0,
								'objectTypeID' => $contentProvider->objectTypeID,
								'nicevalue' => $contentProvider->nicevalue ?: 0,
								'user' => [],
							];
						}
						
						$this->data['provider'][$contentProvider->objectType]['user'][$user->userID] = [
							'count' => $count,
						];
						$this->data['provider'][$contentProvider->objectType]['count'] += $count;
						
						$this->data['count'] += ceil($count / $this->limit) * $this->limit;
					}
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
		
		$userIDs = array_keys($this->data['provider'][$providerObjectType]['user']);
		$userID = array_shift($userIDs);
		$user = new User($userID);
		
		$objectList = $processor->getContentListForUser($user);
		$objectList->sqlLimit = $this->limit;
		$objectList->readObjectIDs();
		$processor->deleteContent($objectList->objectIDs);
		
		$this->data['provider'][$providerObjectType]['user'][$userID]['count'] -= $this->limit;
		
		if ($this->data['provider'][$providerObjectType]['user'][$userID]['count'] <= 0) {
			unset($this->data['provider'][$providerObjectType]['user'][$userID]);
		} 
		
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
		
		$dataArray[$this->generateKey()] = $this->data;
		
		WCF::getSession()->register(self::USER_CONTENT_REMOVE_WORKER_SESSION_NAME, $dataArray);
		
		ClipboardHandler::getInstance()->unmark(array_map(function (User $user) {
			return $user->userID;
		}, $this->user), ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user'));
	}
	
	/**
	 * @inheritDoc
	 */
	public function getProceedURL() {
		return LinkHandler::getInstance()->getLink('UserList');
	}
	
	/**
	 * Generates a key for session data saving.
	 */
	protected function generateKey(): string {
		$userIDs = array_map(function (User $user) {
			return $user->userID;
		}, $this->user);
		sort($userIDs);
		
		return sha1(implode(';', $userIDs));
	}
}
