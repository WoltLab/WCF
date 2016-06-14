<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Condition implementation for the current time.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class TimeCondition extends AbstractMultipleFieldsCondition implements IContentCondition {
	/**
	 * end time
	 * @var	string
	 */
	protected $endTime = '00:00';
	
	/**
	 * @inheritDoc
	 */
	protected $labels = [
		'time' => 'wcf.date.time',
		'timezone' => 'wcf.date.timezone'
	];
	
	/**
	 * start time
	 * @var	string
	 */
	protected $startTime = '00:00';
	
	/**
	 * timezone used to evaluate the start/end time
	 * @var	string
	 */
	protected $timezone = 0;
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		$data = [];
		
		if ($this->startTime) {
			$data['startTime'] = $this->startTime;
		}
		if ($this->endTime) {
			$data['endTime'] = $this->endTime;
		}
		
		if (!empty($data) && $this->timezone) {
			$data['timezone'] = $this->timezone;
		}
		
		if (!empty($data)) {
			return $data;
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		$start = WCF::getLanguage()->get('wcf.date.period.start');
		$end = WCF::getLanguage()->get('wcf.date.period.end');
		
		return <<<HTML
<dl>
	<dt>{$this->getLabel('time')}</dt>
	<dd>
		<input type="datetime" data-ignore-timezone="1" data-time-only="1" id="startTime" name="startTime" value="{$this->startTime}" placeholder="{$start}" />
		<input type="datetime" data-ignore-timezone="1" data-time-only="1" id="endTime" name="endTime" value="{$this->endTime}" placeholder="{$end}" />
		{$this->getDescriptionElement('time')}
		{$this->getErrorMessageElement('time')}
	</dd>
</dl>
<dl>
	<dt>{$this->getLabel('timezone')}</dt>
	<dd>
		{$this->getTimezoneFieldElement()}
		{$this->getDescriptionElement('timezone')}
		{$this->getErrorMessageElement('timezone')}
	</dd>
</dl>
HTML;
	}
	
	/**
	 * Returns the select element with all available timezones.
	 * 
	 * @return	string
	 */
	protected function getTimezoneFieldElement() {
		$fieldElement = '<select name="timezone" id="timezone"><option value="0"'.($this->timezone ? ' selected="selected"' : '').'>'.WCF::getLanguage()->get('wcf.date.timezone.user').'</option>';
		foreach (DateUtil::getAvailableTimezones() as $timezone) {
			$fieldElement .= '<option value="'.$timezone.'"'.($this->timezone === $timezone ? ' selected="selected"' : '').'>'.WCF::getLanguage()->get('wcf.date.timezone.'.str_replace('/', '.', strtolower($timezone))).'</option>';
		}
		$fieldElement .= '</select>';
		
		return $fieldElement;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['endTime'])) $this->endTime = StringUtil::trim($_POST['endTime']);
		if (isset($_POST['startTime'])) $this->startTime = StringUtil::trim($_POST['startTime']);
		if (isset($_POST['timezone'])) $this->timezone = StringUtil::trim($_POST['timezone']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->endTime = '00:00';
		$this->startTime = '00:00';
		$this->timezone = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		if ($condition->endTime) {
			$this->endTime = $condition->endTime;
		}
		if ($condition->startTime) {
			$this->startTime = $condition->startTime;
		}
		if ($condition->timezone) {
			$this->timezone = $condition->timezone;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->startTime == '00:00' && $this->endTime == '00:00') {
			$this->startTime = $this->endTime = '';
			return;
		}
		
		$startDateTime = $endDateTime = null;
		if ($this->startTime) {
			$startDateTime = \DateTime::createFromFormat('H:i', $this->startTime);
			if ($startDateTime === false) {
				$this->errorMessages['time'] = 'wcf.date.startTime.error.notValid';
				
				throw new UserInputException('startTime', 'notValid');
			}
		}
		if ($this->endTime) {
			$endDateTime = \DateTime::createFromFormat('H:i', $this->endTime);
			if ($endDateTime === false) {
				$this->errorMessages['time'] = 'wcf.date.endTime.error.notValid';
				
				throw new UserInputException('endTime', 'notValid');
			}
		}
		
		if ($startDateTime !== null && $endDateTime !== null) {
			if ($startDateTime->getTimestamp() >= $endDateTime->getTimestamp()) {
				$this->errorMessages['time'] = 'wcf.date.endTime.error.beforeStartTime';
				
				throw new UserInputException('endTime', 'beforeStartTime');
			}
		}
		
		if ($this->timezone && !in_array($this->timezone, DateUtil::getAvailableTimezones())) {
			$this->errorMessages['timezone'] = 'wcf.global.form.error.notValidSelection';
			
			throw new UserInputException('timezone', 'notValidSelection');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function showContent(Condition $condition) {
		$timezone = WCF::getUser()->getTimeZone();
		if ($condition->timezone) {
			$timezone = new \DateTimeZone($condition->timezone);
		}
		
		if ($condition->startTime) {
			$dateTime = \DateTime::createFromFormat('H:i', $condition->startTime, $timezone);
			if ($dateTime->getTimestamp() > TIME_NOW) {
				return false;
			}
		}
		
		if ($condition->endTime) {
			$dateTime = \DateTime::createFromFormat('H:i', $condition->endTime, $timezone);
			if ($dateTime->getTimestamp() < TIME_NOW) {
				return false;
			}
		}
		
		return true;
	}
}
