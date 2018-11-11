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
 * @author	Joshua Rueweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 * @since       3.2
 */
class UserContentRemoveWorker extends AbstractWorker implements IWorker {
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
	 * @inheritDoc
	 */
	public function validate() {
		if (!isset($this->parameters['userID'])) {
			throw new \InvalidArgumentException("userID missing");
		}
		
		$this->user = new User($this->parameters['userID']);
		
		if (!$this->user->canEdit()) {
			throw new PermissionDeniedException();
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
		
		foreach ($contentProviders as $contentProvider) {
			/** @var IUserContentProvider $processor */
			$processor = $contentProvider->getProcessor();
			$contentList = $processor->getContentListForUser($this->user);
			$count = $contentList->countObjects();
			
			if ($count) {
				$this->data['provider'][$contentProvider->objectTypeID] = [
					'processor' => $processor,
					'count' => $count
				];
				
				$this->data['count'] += ceil($count / $this->limit) * $this->limit;
			}
		}
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
		$providerID = array_pop($values);
		$provider = $this->data['provider'][$providerID];
		/** @var IUserContentProvider $processor */
		$processor = $provider['processor'];
		
		$objectList = $processor->getContentListForUser($this->user);
		$objectList->sqlLimit = $this->limit;
		$objectList->readObjectIDs();
		$processor->deleteContent($objectList->objectIDs);
		
		$this->data['provider'][$providerID]['count'] -= $this->limit;
		
		if ($this->data['provider'][$providerID]['count'] <= 0) {
			unset($this->data['provider'][$providerID]);
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
