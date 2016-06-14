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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class ModerationActivationForm extends AbstractModerationForm {
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$processor = ModerationQueueManager::getInstance()->getProcessor(null, null, $this->queue->objectTypeID);
		if (!($processor instanceof IModerationQueueActivationHandler)) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'disabledContent' => ModerationQueueActivationManager::getInstance()->getDisabledContent($this->queue),
			'queueManager' => ModerationQueueActivationManager::getInstance()
		]);
	}
}
