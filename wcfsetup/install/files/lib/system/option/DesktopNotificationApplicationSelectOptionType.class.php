<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option type implementation for the desktop notification application selection. This
 * option is intentionally designed to be invisible at most times.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class DesktopNotificationApplicationSelectOptionType extends AbstractOptionType {
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		return WCF::getTPL()->fetch('desktopNotificationApplicationSelectOptionType', 'wcf', [
			'applications' => ApplicationHandler::getInstance()->getApplications(),
			'isMultiDomainSetup' => ApplicationHandler::getInstance()->isMultiDomainSetup(),
			'option' => $option,
			'value' => $value
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		if (ApplicationHandler::getInstance()->isMultiDomainSetup()) {
			if (ApplicationHandler::getInstance()->getApplicationByID($newValue) === null) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
}
