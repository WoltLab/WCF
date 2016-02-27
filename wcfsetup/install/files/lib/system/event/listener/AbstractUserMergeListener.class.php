<?php
namespace wcf\system\event\listener;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Abstract implementation of an event listener updating database tables during
 * a user merge.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.event.listener
 * @category	Community Framework
 */
abstract class AbstractUserMergeListener implements IParameterizedEventListener {
	/**
	 * data of the updated database tables
	 * can either contain the database table as value if `userID` is the name
	 * of the database column and no ignore is needed or an array with values
	 * `name` (database table name), `userID`(name of the database table column
	 * containing the id of the user) and `ignore` (optional) if an UPDATE IGNORE
	 * query should be used.
	 * `{WCF_N}` will be automatically replaced with the number of the WCF installation
	 * (only with PHP 5.6 string concatenation is possible in property declarations)
	 * @var	array
	 */
	protected $databaseTables = [];
	
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		foreach ($this->databaseTables as $databaseTable) {
			if (!is_array($databaseTable)) {
				$databaseTable = ['name' => $databaseTable];
			}
			if (!isset($databaseTable['userID'])) $databaseTable['userID'] = 'userID';
			
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add($databaseTable['userID']." IN (?)", [$eventObj->mergedUserIDs]);
			
			$sql = "UPDATE".(!empty($databaseTable['ignore']) ? " IGNORE" : "")."	".str_replace('{WCF_N}', WCF_N, $databaseTable['name'])."
				SET	".$databaseTable['userID']." = ?
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array_merge([$eventObj->destinationUserID], $conditionBuilder->getParameters()));
		}
	}
}
