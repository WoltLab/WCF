<?php
namespace wcf\acp\action;
use wcf\action\AbstractAction;
use wcf\data\package\PackageCache;
use wcf\data\user\avatar\DefaultAvatar;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\system\database\statement\PreparedStatement;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Exports the stored data of an user in compliance with Art. 20 "Right to data portability" of the
 * the General Data Protection Regulation (GDPR) of the European Union.
 * 
 * The file formats XML, JSON and CSV are explicitly listed as being a structured
 * and machine-readable format by the European Commission.
 * See https://ec.europa.eu/info/law/law-topic/data-protection/reform/rules-business-and-organisations/dealing-citizens/can-individuals-ask-have-their-data-transferred-another-organisation_en  
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category	Community Framework
 */
class UserExportGdprAction extends AbstractAction {
	/**
	 * export data
	 * @var array
	 */
	public $data = array();
	
	/**
	 * list of column names of the user table, the columns `languageID` and `registrationIpAddress`
	 * are not listed here, but included in the final output due to special handling
	 * @var string[]
	 */
	public $exportUserPropertiesIfNotEmpty = array('username', 'email', 'registrationDate', 'oldUsername', 'lastUsernameChange', 'signature', 'lastActivityTime', 'userTitle');
	
	/**
	 * list of user options that are associated with a settings.* category, but should be included
	 * in the export regardless
	 * @var string[]
	 */
	public $exportUserOptionSettingsIfNotEmpty = array('timezone');
	
	/**
	 * list of database tables that hold ip addresses, the identifier is used to check if the
	 * package is installed and on success exports the data from all listed table names
	 * @var string[][]
	 */
	public $ipAddresses = array();
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = array('admin.user.canEditUser');
	
	/**
	 * list of user option names that are excluded from the output, any option that begins with
	 * `can*` or `admin*` are excluded by default, as well as any option that ends with `*perPage`
	 * @var string[]
	 */
	public $skipUserOptions = array('showSignature', 'watchThreadOnReply');
	
	/**
	 * @var UserProfile
	 */
	public $user;
	
	/**
	 * @var integer
	 */
	public $userID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['id'])) $this->userID = intval($_GET['id']);
		
		$this->user = UserProfile::getUserProfile($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
		
		if (!UserGroup::isAccessibleGroup($this->user->getGroupIDs())) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		// you MUST NOT use the `execute` event to provide data, use `export` (see below) instead!
		parent::execute();
		
		$this->ipAddresses = array(
			'com.woltlab.blog' => array('blog'.WCF_N.'_entry '),
			'com.woltlab.calendar' => array('calendar'.WCF_N.'_event'),
			// do not include filebaseN_file_version here, it lacks a userID column and therefore we cannot
			// reliably determine if that ip address belongs to the file author, or if it was somebody else,
			// e. g. moderators or other authors
			'com.woltlab.filebase' => array('filebase'.WCF_N.'_file', 'filebase'.WCF_N.'_file_download'),
			'com.woltlab.gallery' => array(), // intentionally left empty, the image table is queried manually
			'com.woltlab.wbb' => array('wbb'.WCF_N.'_post'),
			'com.woltlab.wcf.conversation' => array('wcf'.WCF_N.'_conversation_message'),
		);
		
		// content
		$this->data = array(
			'com.woltlab.wcf' => array(
				'user' => $this->exportUser(),
				'userOptions' => $this->exportUserOptions(),
				'ipAddresses' => $this->exportSessionIpAddresses(),
			)
		);
		
		EventHandler::getInstance()->fireAction($this, 'export');
		
		foreach ($this->ipAddresses as $package => $tableNames) {
			if (PackageCache::getInstance()->getPackageByIdentifier($package) === null) {
				continue;
			}
			
			$ipAddresses = array();
			foreach ($tableNames as $tableName) {
				$ipAddresses = array_merge(
					$ipAddresses,
					$this->exportIpAddresses($tableName, 'ipAddress', 'time', 'userID')
				);
			}
			
			if ($package === 'com.woltlab.gallery') {
				$ipAddresses = array_merge(
					$ipAddresses,
					$this->exportIpAddresses('gallery'.WCF_N.'_image', 'ipAddress', 'uploadTime', 'userID')
				);
			}
			
			if (!empty($ipAddresses)) {
				if (!isset($this->data[$package])) $this->data[$package] = array();
				$this->data[$package]['ipAddresses'] = $ipAddresses;
			}
		}
		
		$this->data['@@generator'] = array(
			'software' => 'WoltLab Community Framework',
			'version' => WCF_VERSION,
			'generatedAt' => TIME_NOW
		);
		
		// header
		@header('Content-type: application/json');
		@header('Content-disposition: attachment; filename="user-export-gdpr-'.$this->user->userID.'.json"');
		
		// no cache headers
		@header('Pragma: no-cache');
		@header('Expires: 0');
		
		echo json_encode($this->data, JSON_PRETTY_PRINT);
		
		$this->executed();
		exit;
	}
	
	/**
	 * Exports the list of stored ip addresses for this user using the IPv4 representation
	 * whenever possible.
	 * 
	 * @param       string          $databaseTable
	 * @param       string          $ipAddressColumn
	 * @param       string          $timeColumn
	 * @param       string          $userIDColumn
	 * @return      array
	 */
	public function exportIpAddresses($databaseTable, $ipAddressColumn, $timeColumn, $userIDColumn) {
		$sql = "SELECT  ${ipAddressColumn}, ${timeColumn}
			FROM    ${databaseTable}
			WHERE   ${userIDColumn} = ?
				AND {$ipAddressColumn} <> ''";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->user->userID));
		
		return $this->fetchIpAddresses($statement, $ipAddressColumn, $timeColumn);
	}
	
	protected function fetchIpAddresses(PreparedStatement $statement, $ipAddressColumn, $timeColumn) {
		$ipAddresses = array();
		while ($row = $statement->fetchArray()) {
			if (!$row[$ipAddressColumn]) continue;
			
			$ipAddresses[] = array(
				'ipAddress' => UserUtil::convertIPv6To4($row[$ipAddressColumn]),
				'time' => $row[$timeColumn]
			);
		}
		
		return $ipAddresses;
	}
	
	protected function exportSessionIpAddresses() {
		$exportFromVirtual = function($sessionTable, $sessionVirtualTable) {
			$sql = "SELECT  sv.ipAddress, sv.lastActivityTime
				FROM    ${sessionVirtualTable} sv
				WHERE   sv.sessionID IN (
						SELECT  sessionID
						FROM    ${sessionTable}
						WHERE   userID = ?
					)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->user->userID));
			
			return $this->fetchIpAddresses($statement, 'ipAddress', 'lastActivityTime');
		};
		
		if (SESSION_ENABLE_VIRTUALIZATION) {
			// we can safely ignore the wcfN_session table in this case, because its data is
			// just mirrored from the virtual session table, except that it shows the data
			// from the last client only
			$ipAddresses = $exportFromVirtual('wcf'.WCF_N.'_session', 'wcf'.WCF_N.'_session_virtual');
		}
		else {
			$ipAddresses = $this->exportIpAddresses('wcf'.WCF_N.'_session', 'ipAddress', 'lastActivityTime', 'userID');
		}
		
		$ipAddresses = array_merge(
			$ipAddresses,
			$this->exportIpAddresses('wcf'.WCF_N.'_acp_session', 'ipAddress', 'lastActivityTime', 'userID')
		);
		
		// we can ignore the wcfN_acp_session_access_log table because it is directly related
		// to the wcfN_acp_session_log table and ACP sessions are bound to the ip address 
		$ipAddresses = array_merge(
			$ipAddresses,
			$this->exportIpAddresses('wcf'.WCF_N.'_acp_session_log', 'ipAddress', 'lastActivityTime', 'userID')
		);
		
		return $ipAddresses;
	}
	
	protected function exportUser() {
		$data = array(
			'languageCode' => $this->user->getLanguage()->getFixedLanguageCode(),
		);
		if ($this->user->registrationIpAddress) $data['registrationIpAddress'] = UserUtil::convertIPv6To4($this->user->registrationIpAddress);
		
		foreach ($this->exportUserPropertiesIfNotEmpty as $property) {
			if ($this->user->{$property}) $data[$property] = $this->user->{$property};
		}
		
		if ($this->user->avatarID || (MODULE_GRAVATAR && $this->user->enableGravatar)) {
			$data['avatarURL'] = $this->user->getAvatar()->getURL();
		}
		
		return $data;
	}
	
	protected function exportUserOptions() {
		$optionHandler = new UserOptionHandler(false, '', '');
		$optionHandler->init();
		$optionTree = $optionHandler->getOptionTree();
		
		$data = array();
		foreach ($optionTree as $category) {
			$this->exportUserOptionCategory($data, $category);
		}
		
		return $data;
	}
	
	protected function exportUserOptionCategory(array &$data, array $optionTree) {
		if (!empty($optionTree['options'])) {
			foreach ($optionTree['options'] as $optionData) {
				$option = $optionData['object'];
				
				if (in_array($option->optionName, $this->skipUserOptions)) {
					// blacklisted option name
					continue;
				}
				else if (preg_match('~(?:^(?:admin|can)[A-Z]|PerPage$)~', $option->optionName)) {
					// ignore any option that begins with `admin*` and `can*`, or ends with `*perPage`
					continue;
				}
				
				// ignore settings unless they are explicitly white-listed
				if (strpos($option->categoryName, 'settings.') === 0) {
					if (!in_array($option->optionName, $this->exportUserOptionSettingsIfNotEmpty)) {
						continue;
					}
				}
				
				$optionValue = $this->user->getUserOption($option->optionName);
				if ($option->optionType === 'boolean') {
					$optionValue = ($optionValue == 1);
				}
				else if ($option->optionType === 'select' || $option->optionType === 'timezone') {
					$formattedValue = $this->user->getFormattedUserOption($option->optionName);
					if ($formattedValue) $optionValue = $formattedValue;
				}
				
				// skip empty string values (but not values that resolve to `false` or `0`
				if ($optionValue === '') {
					continue;
				}
				else if ($option->optionName === 'gender' && $optionValue === '0') {
					// exclude the gender if there has been no selection
					continue;
				}
				
				$data[$option->optionName] = $optionValue;
			}
		}
		
		if (!empty($optionTree['categories'])) {
			foreach ($optionTree['categories'] as $category) {
				$this->exportUserOptionCategory($data, $category);
			}
		}
	}
}
