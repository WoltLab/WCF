<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Condition implementation for the days of the week.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class DaysOfWeekCondition extends AbstractMultiSelectCondition implements IContentCondition {
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::$label
	 */
	protected $description = 'wcf.global.multiSelect';
	
	/**
	 * @see	wcf\system\condition\AbstractSelectCondition::$fieldName
	 */
	protected $fieldName = 'daysOfWeek';
	
	/**
	 * @see	\wcf\system\condition\AbstractSingleFieldCondition::$label
	 */
	protected $label = 'wcf.date.daysOfWeek';
	
	/**
	 * @see	\wcf\system\condition\AbstractSelectCondition::getOptions()
	 */
	protected function getOptions() {
		$options = array();
		
		$daysOfWeek = DateUtil::getWeekDays();
		foreach ($daysOfWeek as $index => $day) {
			$options[$index] = WCF::getLanguage()->get('wcf.date.day.'.$day);
		}
		
		return $options;
	}
	
	/**
	 * @see	\wcf\system\condition\IContentCondition::showContent()
	 */
	public function showContent(Condition $condition) {
		$date = DateUtil::getDateTimeByTimestamp(TIME_NOW);
		$date->setTimezone(WCF::getUser()->getTimeZone());
		
		return in_array($date->format('w'), $condition->daysOfWeek);
	}
}
