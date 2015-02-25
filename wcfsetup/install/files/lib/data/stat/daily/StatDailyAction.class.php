<?php
namespace wcf\data\stat\daily;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes statistic-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.stat.daily
 * @category	Community Framework
 */
class StatDailyAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\stat\daily\StatDailyEditor';
	
	/**
	 * Validates the getData action.
	 */
	public function validateGetData() {
		WCF::getSession()->checkPermissions(array('admin.system.canViewLog'));
		
		// validate start date
		if (empty($this->parameters['startDate']) || !preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $this->parameters['startDate'])) {
			throw new UserInputException('startDate');
		}
		
		// validate end date
		if (empty($this->parameters['endDate']) || !preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $this->parameters['endDate'])) {
			throw new UserInputException('endDate');
		}
		
		// validate object types
		if (empty($this->parameters['objectTypeIDs']) || !is_array($this->parameters['objectTypeIDs'])) {
			throw new UserInputException('objectTypeIDs');
		}
		foreach ($this->parameters['objectTypeIDs'] as $objectTypeID) {
			$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
			if ($objectType === null) throw new UserInputException('objectTypeIDs');
		}
		
		// validate date grouping parameter
		if (empty($this->parameters['dateGrouping'])) {
			throw new UserInputException('objectTypeIDs');
		}
	}
	
	/**
	 * Returns the stat data.
	 */
	public function getData() {
		$data = array();
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('objectTypeID IN (?)', array($this->parameters['objectTypeIDs']));
		$conditionBuilder->add('date BETWEEN ? AND ?', array($this->parameters['startDate'], $this->parameters['endDate']));
		
		$limit = 0;
		if ($this->parameters['dateGrouping'] == 'yearly') {
			$sql = "SELECT		MIN(date) AS date, SUM(counter) AS counter, MAX(total) AS total, objectTypeID
				FROM		wcf".WCF_N."_stat_daily
				".$conditionBuilder."
				GROUP BY	EXTRACT(YEAR FROM date), objectTypeID
				ORDER BY	date";
		}
		else if ($this->parameters['dateGrouping'] == 'monthly') {
			$sql = "SELECT		MIN(date) AS date, SUM(counter) AS counter, MAX(total) AS total, objectTypeID
				FROM		wcf".WCF_N."_stat_daily
				".$conditionBuilder."
				GROUP BY	EXTRACT(YEAR_MONTH FROM date), objectTypeID
				ORDER BY	date";
		}
		else if ($this->parameters['dateGrouping'] == 'weekly') {
			$sql = "SELECT		MIN(date) AS date, SUM(counter) AS counter, MAX(total) AS total, objectTypeID
				FROM		wcf".WCF_N."_stat_daily
				".$conditionBuilder."
				GROUP BY	EXTRACT(YEAR FROM date), EXTRACT(WEEK FROM date), objectTypeID
				ORDER BY	date";
			$limit = 260;
		}
		else {
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_stat_daily
				".$conditionBuilder."
				ORDER BY	date";
			$limit = 365;
		}
		
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$value = $row['counter'];
			if (!empty($this->parameters['value']) && $this->parameters['value'] == 'total') $value = $row['total'];
			
			$objectType = ObjectTypeCache::getInstance()->getObjectType($row['objectTypeID']);
			
			if (!isset($data[$row['objectTypeID']])) {
				$data[$row['objectTypeID']] = array(
					'label' => WCF::getLanguage()->get('wcf.acp.stat.'.$objectType->objectType),
					'data' => array()
				);
			}
			
			$data[$row['objectTypeID']]['data'][] = array(strtotime($row['date'] . ' UTC'), $objectType->getProcessor()->getFormattedCounter($value));
		}
		
		return $data;
	}
}
