<?php
namespace wcf\system\database\util;
use wcf\system\exception\SystemException;

/**
 * Builds a sql query 'where' condition for prepared statements.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Util
 */
class PreparedStatementConditionBuilder extends ConditionBuilder {
	/**
	 * input parameters
	 * @var	array
	 */
	protected $parameters = [];
	
	/**
	 * Adds a new condition. The parameters array has to be a numbered array.
	 * 
	 * @param	string		$condition
	 * @param	array		$parameters
	 */
	public function add($condition, array $parameters = []) {
		if (!empty($parameters)) {
			$count = 0;
			$callback = function () use (&$count, $parameters, $condition) {
				if (!array_key_exists($count, $parameters)) {
					throw new SystemException("missing parameter for token number " . ($count + 1) . " in condition '".$condition."'");
				}
				else if (is_array($parameters[$count]) && empty($parameters[$count])) {
					// Only throw an exception if the developer tools are active, preventing this
					// from triggering an error for queries that are never actually executed.
					// 
					// This is done to preserve backwards-compatibility with earlier releases that
					// allowed this kind of issue, effectively relying on the database to bail out.
					if (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS) {
						throw new \RuntimeException("An empty array was passed for token number " . ($count + 1) . " in condition '" . $condition . "'");
					}
				}
				
				$result = '?';
				if (is_array($parameters[$count]) && !empty($parameters[$count])) {
					$result .= str_repeat(',?', count($parameters[$count]) - 1);
				}
				
				$count++;
				return $result;
			};
			
			$condition = preg_replace_callback('/\?/', $callback, $condition);
		}
		
		// add condition
		if (!empty($this->conditions)) $this->conditions .= $this->concat;
		$this->conditions .= $condition;
		
		// parameter handling
		if (!empty($parameters)) {
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
