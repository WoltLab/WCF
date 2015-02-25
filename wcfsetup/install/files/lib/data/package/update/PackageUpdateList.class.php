<?php
namespace wcf\data\package\update;
use wcf\data\DatabaseObjectList;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Represents a list of package updates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update
 * @category	Community Framework
 */
class PackageUpdateList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\package\update\PackageUpdate';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::__construct()
	 */
	public function __construct($useSqlOr = false) {
		parent::__construct();
		
		if ($useSqlOr) {
			$this->conditionBuilder = new PreparedStatementConditionBuilder(true, 'OR');
		}
	}
}
