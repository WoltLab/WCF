<?php
namespace wcf\data\notice;
use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\system\condition\ConditionHandler;
use wcf\system\request\IRouteController;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Represents a notice.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Notice
 *
 * @property-read	integer		$noticeID
 * @property-read	string		$noticeName
 * @property-read	string		$notice
 * @property-read	integer		$noticeUseHtml
 * @property-read	string		$cssClassName
 * @property-read	integer		$showOrder
 * @property-read	integer		$isDisabled
 * @property-read	integer		$isDismissible
 */
class Notice extends DatabaseObject implements IRouteController {
	/**
	 * true if the active user has dismissed the notice
	 * @var	boolean
	 */
	protected $isDismissed = null;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'notice';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'noticeID';
	
	/**
	 * Returns the textual representation of the notice.
	 * 
	 * @return	string
	 * @since	3.0
	 */
	public function __toString() {
		// replace `{$username}` with the active user's name and `{$email}`
		// with the active user's email address
		$text = strtr(WCF::getLanguage()->get($this->notice), [
			'{$username}' => WCF::getUser()->username,
			'{$email}' => WCF::getUser()->email
		]);
		
		if (!$this->noticeUseHtml) {
			$text = nl2br(htmlspecialchars($text), false);
		}
		
		return $text;
	}
	
	/**
	 * Returns the conditions of the notice.
	 * 
	 * @return	Condition[]
	 */
	public function getConditions() {
		return ConditionHandler::getInstance()->getConditions('com.woltlab.wcf.condition.notice', $this->noticeID);
	}
	
	/**
	 * @inheritDoc
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
					$statement->execute([
						WCF::getUser()->userID
					]);
					
					$noticeIDs = [];
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
