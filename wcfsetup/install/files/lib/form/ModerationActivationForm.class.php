<?php
namespace wcf\form;
use wcf\system\moderation\queue\ModerationQueueActivationManager;
use wcf\system\WCF;

/**
 * Shows the moderation activation form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class ModerationActivationForm extends AbstractModerationForm {
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'disabledContent' => ModerationQueueActivationManager::getInstance()->getDisabledContent($this->queue)
		));
	}
}
