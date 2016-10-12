<?php
namespace wcf\system\database\util;

/**
 * Builds a sql query 'where' condition.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Util
 */
class ConditionBuilder {
	/**
	 * must be true to add the 'WHERE' keyword automatically
	 * @var	boolean
	 */
	protected $addWhereKeyword = true;
	
	/**
	 * string used for concatenating conditions
	 * @var	string
	 */
	protected $concat = '';
	
	/**
	 * conditions string
	 * @var	string
	 */
	protected $conditions = '';
	
	/**
	 * Creates a new ConditionBuilder object.
	 * 
	 * @param	boolean		$addWhereKeyword
	 * @param	string		$concat
	 */
	public function __construct($addWhereKeyword = true, $concat = 'AND') {
		$this->addWhereKeyword = $addWhereKeyword;
		$this->concat = ($concat == 'OR') ? ' OR ' : ' AND ';
	}
	
	/**
	 * Adds a new condition.
	 * 
	 * @param	mixed		$conditions
	 */
	public function add($conditions) {
		if (!is_array($conditions)) $conditions = [$conditions];
		
		foreach ($conditions as $condition) {
			if (!empty($this->conditions)) $this->conditions .= $this->concat;
			$this->conditions .= $condition;
		}
	}
	
	/**
	 * Returns the build condition.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return (($this->addWhereKeyword && $this->conditions) ? 'WHERE ' : '').$this->conditions;
	}
	
	/**
	 * Enables / disables the where keyword.
	 * 
	 * @param	boolean		$enable
	 */
	public function enableWhereKeyword($enable = true) {
		$this->addWhereKeyword = $enable;
	}
}
