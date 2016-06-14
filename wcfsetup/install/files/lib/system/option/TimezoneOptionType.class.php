<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Option type implementation for a select list with the available time zones.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class TimezoneOptionType extends AbstractOptionType {
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		$timezoneOptions = [];
		foreach (DateUtil::getAvailableTimezones() as $timezone) {
			$timezoneOptions[$timezone] = WCF::getLanguage()->get('wcf.date.timezone.'.str_replace('/', '.', strtolower($timezone)));
		}
		
		WCF::getTPL()->assign([
			'option' => $option,
			'selectOptions' => $timezoneOptions,
			'value' => ($value ?: TIMEZONE)
		]);
		return WCF::getTPL()->fetch('selectOptionType');
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		if (!in_array($newValue, DateUtil::getAvailableTimezones())) {
			throw new UserInputException($option->optionName, 'validationFailed');
		}
	}
}
