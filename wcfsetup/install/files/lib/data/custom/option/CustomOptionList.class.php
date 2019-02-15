<?php
namespace wcf\data\custom\option;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Custom\Option
 * @since	3.1
 * 
 * @method	CustomOption		current()
 * @method	CustomOption[]		getObjects()
 * @method	CustomOption|null	search($objectID)
 * @property	CustomOption[]		$objects
 */
abstract class CustomOptionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = CustomOption::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'showOrder';
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();
		
		$this->sqlSelects = "CONCAT('customOption', CAST({$this->getDatabaseTableAlias()}.optionID AS CHAR)) AS optionName";
	}
}
