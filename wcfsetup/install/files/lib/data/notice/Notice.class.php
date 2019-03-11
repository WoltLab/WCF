<?php
namespace wcf\data\notice;
use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\system\condition\ConditionHandler;
use wcf\system\request\IRouteController;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a notice.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Notice
 *
 * @property-read	integer		$noticeID		unique id of the notice
 * @property-read	string		$noticeName		name of the notice shown in ACP
 * @property-read	string		$notice			text of the notice or name of language item which contains the text
 * @property-read	integer		$noticeUseHtml		is `1` if the notice text will be rendered as HTML, otherwise `0`
 * @property-read	string		$cssClassName		css class name(s) used for the notice HTML element
 * @property-read	integer		$showOrder		position of the notice in relation to the other notices
 * @property-read	integer		$isDisabled		is `1` if the notice is disabled and thus not shown, otherwise `0`
 * @property-read	integer		$isDismissible		is `1` if the notice can be dismissed by users, otherwise `0`
 */
class Notice extends DatabaseObject implements IRouteController {
	/**
	 * true if the active user has dismissed the notice
	 * @var	boolean
	 */
	protected $isDismissed = null;
	
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
			'{$username}' => $this->noticeUseHtml ? StringUtil::encodeHTML(WCF::getUser()->username) : WCF::getUser()->username,
			'{$email}' => $this->noticeUseHtml ? StringUtil::encodeHTML(WCF::getUser()->email) : WCF::getUser()->email
		]);
		
		if (!$this->noticeUseHtml) {
			$text = nl2br(StringUtil::encodeHTML($text), false);
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
