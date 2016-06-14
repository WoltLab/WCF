<?php
namespace wcf\data\user\object\watch;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes watched object-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Object\Watch
 * 
 * @method	UserObjectWatch			create()
 * @method	UserObjectWatchEditor[]		getObjects()
 * @method	UserObjectWatchEditor		getSingleObject()
 */
class UserObjectWatchAction extends AbstractDatabaseObjectAction {
	/**
	 * object type object
	 * @var	\wcf\data\object\type\ObjectType
	 */
	protected $objectType = null;
	
	/**
	 * user object watch object
	 * @var	\wcf\data\user\object\watch\UserObjectWatch
	 */
	protected $userObjectWatch = null;
	
	/**
	 * Validates parameters to manage a subscription.
	 */
	public function validateManageSubscription() {
		$this->readInteger('objectID');
		$this->readString('objectType');
		
		// validate object type
		$this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $this->parameters['objectType']);
		if ($this->objectType === null) {
			throw new UserInputException('objectType');
		}
		
		// validate object id
		$this->objectType->getProcessor()->validateObjectID($this->parameters['objectID']);
		
		// get existing subscription
		$this->userObjectWatch = UserObjectWatch::getUserObjectWatch($this->objectType->objectTypeID, WCF::getUser()->userID, $this->parameters['objectID']);
	}
	
	/**
	 * Returns a form to manage a subscription.
	 * 
	 * @return	array
	 */
	public function manageSubscription() {
		WCF::getTPL()->assign([
			'objectType' => $this->objectType,
			'userObjectWatch' => $this->userObjectWatch
		]);
		
		return [
			'objectID' => $this->parameters['objectID'],
			'template' => WCF::getTPL()->fetch('manageSubscription')
		];
	}
	
	/**
	 * Validates parameters to save subscription state.
	 */
	public function validateSaveSubscription() {
		$this->readBoolean('enableNotification');
		$this->readBoolean('subscribe');
		
		$this->validateManageSubscription();
	}
	
	/**
	 * Saves subscription state.
	 */
	public function saveSubscription() {
		// subscribe
		if ($this->parameters['subscribe']) {
			// newly subscribed
			if ($this->userObjectWatch === null) {
				UserObjectWatchEditor::create([
					'notification' => ($this->parameters['enableNotification'] ? 1 : 0),
					'objectID' => $this->parameters['objectID'],
					'objectTypeID' => $this->objectType->objectTypeID,
					'userID' => WCF::getUser()->userID
				]);
			}
			else if ($this->userObjectWatch->notification != $this->parameters['enableNotification']) {
				// update notification type
				$editor = new UserObjectWatchEditor($this->userObjectWatch);
				$editor->update([
					'notification' => ($this->parameters['enableNotification'] ? 1 : 0)
				]);
			}
			
			// reset user storage
			$this->objectType->getProcessor()->resetUserStorage([WCF::getUser()->userID]);
		}
		else if ($this->userObjectWatch !== null) {
			// unsubscribe
			$editor = new UserObjectWatchEditor($this->userObjectWatch);
			$editor->delete();
			
			// reset user storage
			$this->objectType->getProcessor()->resetUserStorage([WCF::getUser()->userID]);
		}
		
		return [
			'objectID' => $this->parameters['objectID'],
			'subscribe' => $this->parameters['subscribe']
		];
	}
	
	/**
	 * Adds a subscription.
	 */
	public function subscribe() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $this->parameters['data']['objectType']);
		
		UserObjectWatchEditor::create([
			'userID' => WCF::getUser()->userID,
			'objectID' => intval($this->parameters['data']['objectID']),
			'objectTypeID' => $objectType->objectTypeID,
			'notification' => (!empty($this->parameters['enableNotification']) ? 1 : 0)
		]);
		
		// reset user storage
		$objectType->getProcessor()->resetUserStorage([WCF::getUser()->userID]);
	}
	
	/**
	 * Removes a subscription.
	 */
	public function unsubscribe() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $this->parameters['data']['objectType']);
		
		if ($this->userObjectWatch !== null) $userObjectWatch = $this->userObjectWatch;
		else {
			$userObjectWatch = UserObjectWatch::getUserObjectWatch($objectType->objectTypeID, WCF::getUser()->userID, intval($this->parameters['data']['objectID']));
		}
		$editor = new UserObjectWatchEditor($userObjectWatch);
		$editor->delete();
		
		// reset user storage
		$objectType->getProcessor()->resetUserStorage([WCF::getUser()->userID]);
	}
	
	/**
	 * Validates the subscribe action.
	 */
	protected function __validateSubscribe() {
		$this->readInteger('objectID', false, 'data');
		$this->readString('objectType', false, 'data');
		
		// validate object type
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', $this->parameters['data']['objectType']);
		if ($objectType === null) {
			throw new UserInputException('objectType');
		}
		
		// validate object id
		$objectType->getProcessor()->validateObjectID(intval($this->parameters['data']['objectID']));
		
		// get existing subscription
		$this->userObjectWatch = UserObjectWatch::getUserObjectWatch($objectType->objectTypeID, WCF::getUser()->userID, intval($this->parameters['data']['objectID']));
	}
	
	/**
	 * Validates the subscribe action.
	 */
	public function validateSubscribe() {
		$this->__validateSubscribe();
		
		if ($this->userObjectWatch !== null) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Validates the unsubscribe action.
	 */
	public function validateUnsubscribe() {
		$this->__validateSubscribe();
		
		if ($this->userObjectWatch === null) {
			throw new PermissionDeniedException();
		}
	}
}
