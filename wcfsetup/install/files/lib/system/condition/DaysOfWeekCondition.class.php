<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Condition implementation for the days of the week.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
class DaysOfWeekCondition extends AbstractMultiSelectCondition implements IContentCondition {
	/**
	 * @inheritDoc
	 */
	protected $description = 'wcf.global.multiSelect';
	
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'daysOfWeek';
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.date.daysOfWeek';
	
	/**
	 * @inheritDoc
	 */
	protected function getOptions() {
		$options = [];
		
		$daysOfWeek = DateUtil::getWeekDays();
		foreach ($daysOfWeek as $index => $day) {
			$options[$index] = WCF::getLanguage()->get('wcf.date.day.'.$day);
		}
		
		return $options;
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		$date = DateUtil::getDateTimeByTimestamp(TIME_NOW);
		$date->setTimezone(WCF::getUser()->getTimeZone());
		
		return in_array($date->format('w'), $condition->daysOfWeek);
	}
}
