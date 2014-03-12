<?php
namespace wcf\system\importer;
use wcf\data\user\group\UserGroup;
use wcf\data\user\option\UserOptionList;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Imports users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserImporter extends AbstractImporter {
	/**
	 * @see	\wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\user\User';
	
	/**
	 * ids of default notification events
	 * @var	array<integer>
	 */
	protected $eventIDs = array();
	
	/**
	 * list of user options
	 * @var	array<\wcf\data\user\option\UserOption>
	 */
	protected $userOptions = array();
	
	/**
	 * Creates a new UserImporter object.
	 */
	public function __construct() {
		// get default notification events
		$sql = "SELECT	eventID
			FROM	wcf".WCF_N."_user_notification_event
			WHERE	preset = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(1));
		while ($row = $statement->fetchArray()) {
			$this->eventIDs[] = $row['eventID'];
		}
		
		$userOptionList = new UserOptionList();
		$userOptionList->readObjects();
		$this->userOptions = $userOptionList->getObjects();
	}
	
	/**
	 * @see	\wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		// resolve duplicates
		if (ImportHandler::getInstance()->getUserMergeMode() == 4) {
			$existingUser = User::getUserByEmail($data['email']);
			if ($existingUser->userID) {
				// merge user
				ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user', $oldID, $existingUser->userID);
					
				return 0;
			}
		}
		else {
			$existingUser = User::getUserByUsername($data['username']);
			if ($existingUser->userID) {
				if (ImportHandler::getInstance()->getUserMergeMode() == 1 || (ImportHandler::getInstance()->getUserMergeMode() == 3 && mb_strtolower($existingUser->email) != mb_strtolower($data['email']))) {
					// rename user
					$data['username'] = self::resolveDuplicate($data['username']);
				}
				else {
					// merge user
					ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user', $oldID, $existingUser->userID);
					
					return 0;
				}
			}
		}
		
		// check existing user id
		if (is_numeric($oldID)) {
			$user = new User($oldID);
			if (!$user->userID) $data['userID'] = $oldID;
		}
		
		// handle user options
		$userOptions = array();
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
		
		// create user
		$user = UserEditor::create($data);
		$userEditor = new UserEditor($user);
		
		// updates user options
		$userEditor->updateUserOptions($userOptions);
		
		// save user groups
		$groupIDs = array();
		if (isset($additionalData['groupIDs'])) {
			foreach ($additionalData['groupIDs'] as $oldGroupID) {
				$newGroupID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $oldGroupID);
				if ($newGroupID) $groupIDs[] = $newGroupID;
			}
		}
		
		if (!$user->activationCode) $defaultGroupIDs = UserGroup::getGroupIDsByType(array(UserGroup::EVERYONE, UserGroup::USERS));
		else $defaultGroupIDs = UserGroup::getGroupIDsByType(array(UserGroup::EVERYONE, UserGroup::GUESTS));
		
		$groupIDs = array_merge($groupIDs, $defaultGroupIDs);
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_group
						(userID, groupID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($groupIDs as $groupID) {
			$statement->execute(array(
				$user->userID,
				$groupID
			));
		}
		
		// save languages
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_language
						(userID, languageID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$languageIDs = array();
		if (isset($additionalData['languages'])) {
			foreach ($additionalData['languages'] as $languageCode) {
				$language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
				if ($language !== null) $languageIDs[] = $language->languageID;
			}
		}
		if (empty($languageIDs)) {
			$languageIDs[] = LanguageFactory::getInstance()->getDefaultLanguageID();
		}
		foreach ($languageIDs as $languageID) {
			$statement->execute(array(
				$user->userID,
				$languageID
			));
		}
		
		// save default user events
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_notification_event_to_user
						(userID, eventID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($this->eventIDs as $eventID) {
			$statement->execute(array(
				$user->userID,
				$eventID
			));
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
		$newUsername = '';
		do {
			$i++;
			$newUsername = 'Duplicate'.($i > 1 ? $i : '').' '.$username;
			// try username
			$sql = "SELECT	userID
				FROM	wcf".WCF_N."_user
				WHERE	username = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($newUsername));
			$row = $statement->fetchArray();
			if (empty($row['userID'])) break;
		}
		while (true);
		
		return $newUsername;
	}
}
