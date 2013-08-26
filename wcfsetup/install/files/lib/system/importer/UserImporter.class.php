<?php
namespace wcf\system\importer;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\database\DatabaseException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Imports users.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserImporter extends AbstractImporter {
	/**
	 * @see wcf\system\importer\AbstractImporter::$className
	 */
	protected $className = 'wcf\data\user\User';
	
	/**
	 * list of group memberships
	 * @var array
	 */
	protected $userToGroups = array();
	
	/**
	 * list of user languages
	 * @var array
	 */
	protected $userToLanguages = array();
	
	/**
	 * list of imported user ids
	 * @var array<integer>
	 */
	protected $userIDs = array();
	
	/**
	 * Creates a new UserImporter object.
	 */
	public function __construct() {
		register_shutdown_function(array($this, 'finalizeImport'));
	}
	
	/**
	 * @see wcf\system\importer\IImporter::import()
	 */
	public function import($oldID, array $data, array $additionalData = array()) {
		// resolve duplicates
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
		
		// check existing user id
		if (is_numeric($oldID)) {
			$user = new User($oldID);
			if (!$user->userID) $data['userID'] = $oldID;
		}
		
		// get group ids
		$groupIDs = array();
		if (isset($additionalData['groupIDs'])) {
			foreach ($additionalData['groupIDs'] as $oldGroupID) {
				$newGroupID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $oldGroupID);
				if ($newGroupID) $groupIDs[] = $newGroupID;
			}
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
		}
		
		// handle languages
		$languageIDs = array();
		if (isset($additionalData['languages'])) {
			foreach ($additionalData['languages'] as $languageCode) {
				$language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
				if ($language !== null) $languageIDs[] = $language->languageID;
			}
		}
		
		// create user
		$user = UserEditor::create($data);
		$userEditor = new UserEditor($user);
		
		// updates user options
		$userEditor->updateUserOptions($userOptions);
		
		// store data
		$this->userIDs[] = $user->userID;
		$this->userToGroups[$user->userID] = $groupIDs;
		$this->userToLanguages[$user->userID] = $languageIDs;
		
		// save mapping
		ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user', $oldID, $user->userID);
		
		return $user->userID;
	}
	
	/**
	 * Finalizes the user import.
	 */
	public function finalizeImport() {
		if (empty($this->userIDs)) return;
		
		// save groups
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_group
						(userID, groupID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($this->userToGroups as $userID => $groupIDs) {
			$groupIDs = array_merge($groupIDs, UserGroup::getGroupIDsByType(array(UserGroup::EVERYONE, UserGroup::USERS)));
			
			foreach ($groupIDs as $groupID) {
				$statement->execute(array($userID, $groupID));
			}
		}
		WCF::getDB()->commitTransaction();
		
		// save languages
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_language
						(userID, languageID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($this->userToLanguages as $userID => $languageIDs) {
			if (empty($languageIDs)) $languageIDs = array(LanguageFactory::getInstance()->getDefaultLanguageID());
			foreach ($languageIDs as $languageID) {
				$statement->execute(array($userID, $languageID));
			}
		}
		WCF::getDB()->commitTransaction();
		
		// get default notification events
		$eventIDs = array();
		$sql = "SELECT	eventID
			FROM	wcf".WCF_N."_user_notification_event
			WHERE	preset = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(1));
		while ($row = $statement->fetchArray()) {
			$eventIDs[] = $row['eventID'];
		}
		
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_notification_event_to_user
						(userID, eventID)
			VALUES			(?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($this->userIDs as $userID) {
			foreach ($eventIDs as $eventID) {
				$statement->execute(array($userID, $eventID));
			}
		}
		WCF::getDB()->commitTransaction();
		
		// reset user ids
		$this->userIDs = array();
	}
	
	/**
	 * Revolves duplicate user names.
	 *
	 * @param	string		$username
	 * @return 	string		new username
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
