<?php
namespace wcf\data\notice;
use wcf\data\DatabaseObject;
use wcf\system\condition\ConditionHandler;
use wcf\system\request\IRouteController;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Represents a notice.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.notice
 * @category	Community Framework
 */
class Notice extends DatabaseObject implements IRouteController {
	/**
	 * true if the active user has dismissed the notice
	 * @var	boolean
	 */
	protected $isDismissed = null;
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'notice';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseTableIndexName = 'noticeID';
	
	/**
	 * Returns the conditions of the notice.
	 * 
	 * @return	array<\wcf\data\condition\Condition>
	 */
	public function getConditions() {
		return ConditionHandler::getInstance()->getConditions('com.woltlab.wcf.condition.notice', $this->noticeID);
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return $this->noticeName;
	}
	
	/**
	 * Returns true if the active user has dismissed the notice.
	 * 
	 * @return	boolean
	 */
	public function isDismissed() {
		if (!$this->isDismissible) return false;
		
		if ($this->isDismissed === null) {
			if (WCF::getUser()->userID) {
				$dismissedNotices = UserStorageHandler::getInstance()->getField('dismissedNotices');
				if ($dismissedNotices === null) {
					$sql = "SELECT	noticeID
						FROM	wcf".WCF_N."_notice_dismissed
						WHERE	userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array(
						WCF::getUser()->userID
					));
					
					$noticeIDs = array();
					while ($noticeID = $statement->fetchColumn()) {
						$noticeIDs[] = $noticeID;
						
						if ($noticeID == $this->noticeID) {
							$this->isDismissed = true;
						}
					}
					
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'dismissedNotices', serialize($noticeIDs));
				}
				else {
					$dismissedNoticeIDs = @unserialize($dismissedNotices);
					$this->isDismissed = in_array($this->noticeID, $dismissedNoticeIDs);
				}
			}
			else {
				$dismissedNotices = WCF::getSession()->getVar('dismissedNotices');
				if ($dismissedNotices !== null) {
					$dismissedNotices = @unserialize($dismissedNotices);
					$this->isDismissed = in_array($this->noticeID, $dismissedNotices);
				}
				else {
					$this->isDismissed = false;
				}
			}
		}
		
		return $this->isDismissed;
	}
}
