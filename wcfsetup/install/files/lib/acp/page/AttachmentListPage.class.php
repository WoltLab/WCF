<?php
namespace wcf\acp\page;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class AttachmentListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.attachment.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.attachment.canManageAttachment');
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'uploadTime';
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortOrder
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('attachmentID', 'filename', 'filesize', 'downloads', 'uploadTime', 'lastDownloadTime');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\attachment\AdministrativeAttachmentList';
	
	/**
	 * username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * filename
	 * @var	string
	 */
	public $filename = '';
	
	/**
	 * file type
	 * @var	string
	 */
	public $fileType = '';
	
	/**
	 * available file types
	 * @var	array<string>
	 */
	public $availableFileTypes = array();
	
	/**
	 * attachment stats
	 * @var	array
	 */
	public $stats = array();
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		if (!empty($_REQUEST['filename'])) $this->filename = StringUtil::trim($_REQUEST['filename']);
		if (!empty($_REQUEST['fileType'])) $this->fileType = $_REQUEST['fileType'];
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$objectTypeIDs = array();
		foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.attachment.objectType') as $objectType) {
			if (!$objectType->private) {
				$objectTypeIDs[] = $objectType->objectTypeID;
			}
		}
		
		if (!empty($objectTypeIDs)) $this->objectList->getConditionBuilder()->add('attachment.objectTypeID IN (?)', array($objectTypeIDs));
		else $this->objectList->getConditionBuilder()->add('1 = 0');
		$this->objectList->getConditionBuilder()->add("attachment.tmpHash = ''");
		
		// get data
		$this->stats = $this->objectList->getStats();
		$this->availableFileTypes = $this->objectList->getAvailableFileTypes();
		
		// filter
		if (!empty($this->username)) {
			$user = User::getUserByUsername($this->username);
			if ($user->userID) {
				$this->objectList->getConditionBuilder()->add('attachment.userID = ?', array($user->userID));
			}
		}
		if (!empty($this->filename)) {
			$this->objectList->getConditionBuilder()->add('attachment.filename LIKE ?', array($this->filename.'%'));
		}
		if (!empty($this->fileType)) {
			$this->objectList->getConditionBuilder()->add('attachment.fileType LIKE ?', array($this->fileType));
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'stats' => $this->stats,
			'username' => $this->username,
			'filename' => $this->filename,
			'fileType' => $this->fileType,
			'availableFileTypes' => $this->availableFileTypes
		));
	}
}
