<?php
namespace wcf\system\condition\user;
use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\condition\AbstractSingleFieldCondition;
use wcf\system\condition\IObjectCondition;
use wcf\system\condition\IObjectListCondition;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * User condition for the interval (in days) of their last activity.
 * 
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Condition\User
 * @since       5.3
 */
class UserLastActivityTimeIntervalDaysCondition extends AbstractSingleFieldCondition implements IObjectCondition, IObjectListCondition {
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.condition.lastActivityTimeIntervalDays';
	
	/**
	 * end value of the days interval
	 * @var int|string
	 */
	protected $endDays = '';
	
	/**
	 * start value of the days interval
	 * @var int|string
	 */
	protected $startDays = '';
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '" . UserList::class . "', instance of '".get_class($objectList)."' given.");
		}
		
		/** @noinspection PhpUndefinedFieldInspection */
		if ($this->object->ignoreZeroTime) {
			$objectList->getConditionBuilder()->add('user_table.lastActivityTime <> ?', [0]);
		}
		if (isset($conditionData['startDays'])) {
			$objectList->getConditionBuilder()->add('user_table.lastActivityTime <= ?', [TIME_NOW - $conditionData['startDays'] * 24 * 3600]);
		}
		if (isset($conditionData['endDays'])) {
			$objectList->getConditionBuilder()->add('user_table.lastActivityTime >= ?', [TIME_NOW - $conditionData['endDays'] * 24 * 3600]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkObject(DatabaseObject $object, array $conditionData) {
		if (!($object instanceof User) && !ClassUtil::isDecoratedInstanceOf($object, User::class)) {
			throw new \InvalidArgumentException("Object is no instance of '" . User::class . "', instance of '".get_class($object)."' given.");
		}
		
		if (isset($conditionData['startDays'])) {
			if ($object->lastActivityTime > TIME_NOW - $conditionData['startDays'] * 24 * 3600) {
				return false;
			}
			
			if (isset($conditionData['endDays'])) {
				if ($object->lastActivityTime < TIME_NOW - $conditionData['endDays'] * 24 * 3600) {
					return false;
				}
			}
			
			return true;
		}
		else if (isset($conditionData['endDays']) && $object->lastActivityTime < TIME_NOW - $conditionData['endDays'] * 24 * 3600) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		$data = [];
		
		if (strlen($this->startDays)) {
			$data['startDays'] = $this->startDays;
		}
		if (strlen($this->endDays)) {
			$data['endDays'] = $this->endDays;
		}
		
		if (!empty($data)) {
			return $data;
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getFieldElement() {
		$start = WCF::getLanguage()->get('wcf.date.period.start');
		$end = WCF::getLanguage()->get('wcf.date.period.end');
		$days = WCF::getLanguage()->get('wcf.acp.option.suffix.days');
		
		return <<<HTML
<div class="inputAddon">
	<input type="number" id="userLastActivityTimeIntervalStartDays" name="userLastActivityTimeIntervalStartDays" class="short" min="1" value="{$this->startDays}" placeholder="{$start}">
	<span class="inputSuffix">{$days}</span>
</div>
<div class="inputAddon">
	<input type="number" id="userLastActivityTimeIntervalEndDays" name="userLastActivityTimeIntervalEndDays" class="short" min="1" value="{$this->endDays}" placeholder="{$end}">
	<span class="inputSuffix">{$days}</span>
</div>
HTML;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['userLastActivityTimeIntervalEndDays'])) $this->endDays = $_POST['userLastActivityTimeIntervalEndDays'];
		if (isset($_POST['userLastActivityTimeIntervalStartDays'])) $this->startDays = $_POST['userLastActivityTimeIntervalStartDays'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function reset() {
		$this->endDays = '';
		$this->startDays = '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function setData(Condition $condition) {
		/** @noinspection PhpUndefinedFieldInspection */
		$endDays = $condition->endDays;
		if ($endDays) {
			$this->endDays = $endDays;
		}
		
		/** @noinspection PhpUndefinedFieldInspection */
		$startDays = $condition->startDays;
		if ($startDays) {
			$this->startDays = $startDays;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		$endDays = $startDays = null;
		if (strlen($this->startDays)) {
			$startDays = intval($this->startDays);
			if ($startDays <= 0) {
				$this->errorMessage = 'wcf.user.condition.lastActivityTimeIntervalDays.error.invalidStart';
				
				throw new UserInputException('userLastActivityTimeIntervalDays', 'invalidStart');
			}
		}
		if (strlen($this->endDays)) {
			$endDays = intval($this->endDays);
			if ($endDays <= 0) {
				$this->errorMessage = 'wcf.user.condition.lastActivityTimeIntervalDays.error.invalidEnd';
				
				throw new UserInputException('userLastActivityTimeIntervalDays', 'invalidEnd');
			}
		}
		
		if ($endDays !== null && $startDays !== null && $endDays <= $startDays) {
			$this->errorMessage = 'wcf.user.condition.lastActivityTimeIntervalDays.error.endBeforeStart';
			
			throw new UserInputException('userLastActivityTimeIntervalDays', 'endBeforeStart');
		}
	}
}
