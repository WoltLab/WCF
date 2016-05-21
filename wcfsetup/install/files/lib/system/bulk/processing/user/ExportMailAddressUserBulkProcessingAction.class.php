<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Bulk processing action implementation for exporting mail addresses of users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bulk.processing.user
 * @category	Community Framework
 * @since	2.2
 */
class ExportMailAddressUserBulkProcessingAction extends AbstractUserBulkProcessingAction {
	/**
	 * type of the file the email addresses will be saved in (csv or xml)
	 * @var	string
	 */
	public $fileType = 'csv';
	
	/**
	 * separates the exported email addresses
	 * @var	string
	 */
	public $separator = ',';
	
	/**
	 * encloses the exported email addresses
	 * @var	string
	 */
	public $textSeparator = '"';
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::executeAction()
	 */
	public function executeAction(DatabaseObjectList $objectList) {
		if (!($objectList instanceof UserList)) return;
		
		// send content type
		header('Content-Type: text/'.$this->fileType.'; charset=UTF-8');
		header('Content-Disposition: attachment; filename="export.'.$this->fileType.'"');
		
		if ($this->fileType == 'xml') {
			echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<addresses>\n";
		}
		
		$userCount = count($objectList);
		$i = 0;
		foreach ($objectList as $user) {
			if ($this->fileType == 'xml') {
				echo "<address><![CDATA[".StringUtil::escapeCDATA($user->email)."]]></address>\n";
			}
			else {
				echo $this->textSeparator.$user->email.$this->textSeparator.($i < $userCount ? $this->separator : '');
			}
			
			$i++;
		}
		
		if ($this->fileType == 'xml') {
			echo "</addresses>";
		}
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::getHTML()
	 */
	public function getHTML() {
		return WCF::getTPL()->fetch('exportMailAddressUserBulkProcessing', 'wcf', [
			'fileType' => $this->fileType,
			'separator' => $this->separator,
			'textSeparator' => $this->textSeparator
		]);
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::getObjectList()
	 */
	public function getObjectList() {
		$userList = parent::getObjectList();
		
		$userList->sqlOrderBy = 'user_table.email';
		
		return $userList;
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST['fileType']) && $_POST['fileType'] == 'xml') $this->fileType = $_POST['fileType'];
		if (isset($_POST['separator'])) $this->separator = $_POST['separator'];
		if (isset($_POST['textSeparator'])) $this->textSeparator = $_POST['textSeparator'];
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::reset()
	 */
	public function reset() {
		exit;
	}
}
