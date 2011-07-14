<?php
namespace wcf\system\database\util;

/**
 * Builds a sql query 'where' condition.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database.util
 * @category 	Community Framework
 */
class ConditionBuilder {
	/**
	 * conditions string
	 * @var string
	 */
	protected $conditions = '';
	
	/**
	 * must be true to add the 'WHERE' keyword automatically
	 * @var boolean
	 */
	protected $addWhereKeyword = true;
	
	/**
	 * Creates a new ConditionBuilder object.
	 *
	 * @param	string		$addWhereKeyword
	 */
	public function __construct($addWhereKeyword = true) {
		$this->addWhereKeyword = $addWhereKeyword;
	}
	
	/**
	 * Adds a new condition.
	 * 
	 * @param	mixed		$conditions
	 */
	public function add($conditions) {
		if (!is_array($conditions)) $conditions = array($conditions);
		
		foreach ($conditions as $condition) {
			if (!empty($this->conditions)) $this->conditions .= " AND ";
			else $this->conditions = ($this->addWhereKeyword ? " WHERE " : '');
			
			$this->conditions .= $condition;
		}
	}
	
	/**
	 * Returns the build condition.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->conditions;
	}
}
?>