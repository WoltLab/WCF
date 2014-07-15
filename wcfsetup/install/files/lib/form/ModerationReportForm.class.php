<?php
namespace wcf\form;
use wcf\data\user\UserProfile;
use wcf\data\user\User;
use wcf\system\moderation\queue\ModerationQueueReportManager;
use wcf\system\WCF;

/**
 * Shows the moderation report form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class ModerationReportForm extends AbstractModerationForm {
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$reportUser = UserProfile::getUserProfile($this->queue->userID);
		if ($reportUser === null) $reportUser = new UserProfile(new User(null, array()));
		WCF::getTPL()->assign(array(
			'reportedContent' => ModerationQueueReportManager::getInstance()->getReportedContent($this->queue),
			'queueManager' => ModerationQueueReportManager::getInstance(),
			'reportUser' => $reportUser
		));
	}
}
