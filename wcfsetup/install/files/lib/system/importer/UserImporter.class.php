<?php
namespace wcf\system\importer;
use wcf\data\user\group\UserGroup;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionList;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Imports users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserImporter extends AbstractImporter {
	/**
	 * @inheritDoc
	 */
	protected $className = User::class;
	
	/**
	 * ids of default notification events
	 * @var	integer[]
	 */
	protected $eventIDs = [];
	
	/**
	 * list of user options
	 * @var	UserOption[]
	 */
	protected $userOptions = [];
	
	const MERGE_MODE_EMAIL = 4;
	const MERGE_MODE_USERNAME_OR_EMAIL = 5;
	
	/**
	 * Creates a new UserImporter object.
	 */
	public function __construct() {
		// get default notification events
		$sql = "SELECT	eventID
			FROM	wcf".WCF_N."_user_notification_event
			WHERE	preset = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([1]);
		$this->eventIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		$userOptionList = new UserOptionList();
		$userOptionList->readObjects();
		$this->userOptions = $userOptionList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$targetUser = null;
		
		// whether to perform a merge
		$performMerge = false;
		
		// fetch user with same username
		$conflictingUser = User::getUserByUsername($data['username']);
		switch (ImportHandler::getInstance()->getUserMergeMode()) {
			/** @noinspection PhpMissingBreakStatementInspection */
			case self::MERGE_MODE_USERNAME_OR_EMAIL:
				// merge target will be the conflicting user
				$targetUser = $conflictingUser;
				
				// check whether user exists
				if ($targetUser->userID) {
					$performMerge = true;
					break;
				}
			case self::MERGE_MODE_EMAIL:
				// fetch merge target
				$targetUser = User::getUserByEmail($data['email']);
				// if it exists: perform a merge
				if ($targetUser->userID) $performMerge = true;
			break;
		}
		
		// merge should be performed
		if ($performMerge) {
			ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user', $oldID, $targetUser->userID);
			return 0;
		}
		
		// a conflict arose, but no merge was performed, resolve
		if ($conflictingUser->userID) {
			// rename user
			$data['username'] = self::resolveDuplicate($data['username']);
		}
		
		// check existing user id
		if (is_numeric($oldID)) {
			$user = new User($oldID);
			if (!$user->userID) $data['userID'] = $oldID;
		}
		
		// handle user options
		$userOptions = [];
		if (isset($additionalData['options'])) {
			foreach ($additionalData['options'] as $optionName => $optionValue) {
				if (is_int($optionName)) $optionID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.option', $optionName);
				else $optionID = User::getUserOptionID($optionName);
				
				if ($optionID) {
					$userOptions[$optionID] = $optionValue;
				}
			}
			
			// fix option values
			foreach ($userOptions as $optionID => &$optionValue) {
				switch ($this->userOptions[$optionID]->optionType) {
					case 'boolean':
						if ($optionValue) $optionValue = 1;
						else $optionValue = 0;
						break;
							
					case 'integer':
						$optionValue = intval($optionValue);
						if ($optionValue > 2147483647) $optionValue = 2147483647;
						break;
							
					case 'float':
						$optionValue = floatval($optionValue);
						break;
							
					case 'textarea':
						if (strlen($optionValue) > 16777215) $optionValue = substr($optionValue, 0, 16777215);
						break;
							
					case 'birthday':
					case 'date':
						if (!preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $optionValue)) $optionValue = '0000-00-00';
						break;
						
					default:
						if (strlen($optionValue) > 65535) $optionValue = substr($optionValue, 0, 65535);
				}
			}
		}
		
		$languageIDs = [];
		if (isset($additionalData['languages'])) {
			foreach ($additionalData['languages'] as $languageCode) {
				$language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
				if ($language !== null) $languageIDs[] = $language->languageID;
			}
		}
		if (empty($languageIDs)) {
			$languageIDs[] = LanguageFactory::getInstance()->getDefaultLanguageID();
		}
		
		// assign an interface language
		$data['languageID'] = reset($languageIDs);
		
		// create user
		$user = UserEditor::create($data);
		$userEditor = new UserEditor($user);
		
		// updates user options
		$userEditor->updateUserOptions($userOptions);
		
		// save user groups
		$groupIDs = [];
		if (isset($additionalData['groupIDs'])) {
			foreach ($additionalData['groupIDs'] as $oldGroupID) {
				$newGroupID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $oldGroupID);
				if ($newGroupID) $groupIDs[] = $newGroupID;
			}
		}
		
		if (!$user->activationCode) $defaultGroupIDs = UserGroup::getGroupIDsByType([UserGroup::EVERYONE, UserGroup::USERS]);
		else $defaultGroupIDs = UserGroup::getGroupIDsByType([UserGroup::EVERYONE, UserGroup::GUESTS]);
		
		$groupIDs = array_merge($groupIDs, $defaultGroupIDs);
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_group
						(userID, groupID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($groupIDs as $groupID) {
			$statement->execute([
				$user->userID,
				$groupID
			]);
		}
		
		// save languages
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_language
						(userID, languageID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($languageIDs as $languageID) {
			$statement->execute([
				$user->userID,
				$languageID
			]);
		}
		
		// save default user events
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_notification_event_to_user
						(userID, eventID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($this->eventIDs as $eventID) {
			$statement->execute([
				$user->userID,
				$eventID
			]);
		}
		
		// save mapping
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user', $oldID, $user->userID);
		
		return $user->userID;
	}
	
	/**
	 * Revolves duplicate user names and returns the new user name.
	 * 
	 * @param	string		$username
	 * @return	string
	 */
	private static function resolveDuplicate($username) {
		$i = 0;
		do {
			$i++;
			$newUsername = 'Duplicate'.($i > 1 ? $i : '').' '.$username;
			// try username
			$sql = "SELECT	userID
				FROM	wcf".WCF_N."_user
				WHERE	username = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$newUsername]);
			$row = $statement->fetchArray();
			if (empty($row['userID'])) break;
		}
		while (true);
		
		return $newUsername;
	}
}
