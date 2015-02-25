<?php
namespace wcf\form;
use wcf\system\exception\IllegalLinkException;
use wcf\system\moderation\queue\activation\IModerationQueueActivationHandler;
use wcf\system\moderation\queue\ModerationQueueActivationManager;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * Shows the moderation activation form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class ModerationActivationForm extends AbstractModerationForm {
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$processor = ModerationQueueManager::getInstance()->getProcessor(null, null, $this->queue->objectTypeID);
		if (!($processor instanceof IModerationQueueActivationHandler)) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'disabledContent' => ModerationQueueActivationManager::getInstance()->getDisabledContent($this->queue),
			'queueManager' => ModerationQueueActivationManager::getInstance()
		));
	}
}
