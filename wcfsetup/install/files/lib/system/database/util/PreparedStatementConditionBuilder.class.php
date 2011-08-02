<?php
namespace wcf\system\database\util;
use wcf\system\exception\SystemException;

/**
 * Builds a sql query 'where' condition for prepared statements.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database.util
 * @category 	Community Framework
 */
class PreparedStatementConditionBuilder extends ConditionBuilder {
	/**
	 * input parameters
	 * @var array
	 */
	protected $parameters = array();
	
	/**
	 * Adds a new condition.
	 * 
	 * @param	string		$condition
	 * @param	array		$parameters
	 */
	public function add($condition, array $parameters = array()) {
		if (count($parameters)) {
			$count = 0;
			$callback = function ($matches) use (&$count, $parameters, $condition) {
				if (!array_key_exists($count, $parameters)) {
					throw new SystemException("missing parameter for token number " . ($count + 1) . " in condition '".$condition."'");
				}

				$result = '?';
				if (is_array($parameters[$count]) && count($parameters[$count])) {
					$result .= str_repeat(',?', count($parameters[$count]) - 1);
				}
				
				$count++;
				return $result;
			};
			
			$condition = preg_replace_callback('/\?/', $callback, $condition);
		}
		
		// add condtion
		if (!empty($this->conditions)) $this->conditions .= " AND ";
		else $this->conditions = ($this->addWhereKeyword ? " WHERE " : '');
		$this->conditions .= $condition;
		
		// parameter handling
		if (count($parameters)) {
			foreach ($parameters as $parameter) {
				if (is_array($parameter)) {
					foreach ($parameter as $value) {
						$this->parameters[] = $value;
					}
				}
				else {
					$this->parameters[] = $parameter;
				}
			}
		}
	}
	
	/**
	 * Returns the input parameters.
	 * 
	 * @return	array
	 */
	public function getParameters() {
		return $this->parameters;
	}
}
