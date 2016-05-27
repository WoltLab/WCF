<?php
namespace wcf\data\package\update;
use wcf\data\DatabaseObjectList;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Represents a list of package updates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update
 * @category	Community Framework
 *
 * @method	PackageUpdate		current()
 * @method	PackageUpdate[]		getObjects()
 * @method	PackageUpdate|null	search($objectID)
 * @property	PackageUpdate[]		$objects
 */
class PackageUpdateList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = PackageUpdate::class;
	
	/**
	 * @inheritDoc
	 */
	public function __construct($useSqlOr = false) {
		parent::__construct();
		
		if ($useSqlOr) {
			$this->conditionBuilder = new PreparedStatementConditionBuilder(true, 'OR');
		}
	}
}
