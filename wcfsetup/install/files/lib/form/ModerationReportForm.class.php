<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\exception\IllegalLinkException;
use wcf\system\moderation\queue\report\IModerationQueueReportHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\moderation\queue\ModerationQueueReportManager;
use wcf\system\WCF;

/**
 * Shows the moderation report form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class ModerationReportForm extends AbstractModerationForm {
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$processor = ModerationQueueManager::getInstance()->getProcessor(null, null, $this->queue->objectTypeID);
		if (!($processor instanceof IModerationQueueReportHandler)) {
			throw new IllegalLinkException();
		}
	}
	
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
