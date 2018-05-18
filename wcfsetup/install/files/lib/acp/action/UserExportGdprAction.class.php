<?php
namespace wcf\acp\action;
use wcf\action\AbstractAction;
use wcf\data\package\PackageCache;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
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
		parent::execute();
		
		$this->ipAddresses = array(
			'com.woltlab.blog' => array('blog'.WCF_N.'_entry '),
			'com.woltlab.calendar' => array('calendar'.WCF_N.'_event'),
			'com.woltlab.filebase' => array('filebase'.WCF_N.'_file', 'filebase'.WCF_N.'_file_download', 'filebase'.WCF_N.'_file_version'),
			'com.woltlab.gallery' => array('gallery'.WCF_N.'_image'),
			'com.woltlab.wbb' => array('wbb'.WCF_N.'_post'),
			'com.woltlab.wcf.conversation' => array('wcf'.WCF_N.'_conversation_message'),
		);
		
		// content
		$this->data = array(
			'com.woltlab.wcf' => array(
				'user' => $this->exportUser(),
				'userOptions' => $this->exportUserOptions()
			)
		);
		
		foreach ($this->ipAddresses as $package => $tableNames) {
			if (PackageCache::getInstance()->getPackageByIdentifier($package) === null) {
				continue;
			}
			
			$this->data[$package] = array();
			
			$ipAddresses = array();
			foreach ($tableNames as $tableName) {
				$ipAddresses = array_merge(
					$ipAddresses,
					$this->exportIpAddresses($tableName, 'ipAddress', 'time', 'userID')
				);
			}
			
			$this->data[$package]['ipAddresses'] = $ipAddresses;
		}
		
		EventHandler::getInstance()->fireAction($this, 'export');
		
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
			WHERE   ${userIDColumn} = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->user->userID));
		
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
	
	protected function exportUser() {
		$data = array(
			'languageCode' => $this->user->getLanguage()->getFixedLanguageCode(),
		);
		if ($this->user->registrationIpAddress) $data['registrationIpAddress'] = UserUtil::convertIPv6To4($this->user->registrationIpAddress);
		
		foreach ($this->exportUserPropertiesIfNotEmpty as $property) {
			if ($this->user->{$property}) $data[$property] = $this->user->{$property};
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
				
				$optionValue = $this->user->getUserOption($option->optionName);
				if ($option->optionType === 'boolean') {
					$optionValue = ($optionValue == 1);
				}
				else if ($option->optionType === 'select' || $option->optionType === 'timezone') {
					$formattedValue = $this->user->getFormattedUserOption($option->optionName);
					if ($formattedValue) $optionValue = $formattedValue;
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
