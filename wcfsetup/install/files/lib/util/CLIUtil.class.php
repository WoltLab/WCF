<?php
namespace wcf\util;
use phpline\internal\AnsiUtil;

/**
 * Provide convenience methods for use on command line interface.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class CLIUtil {
	/**
	 * Generates a table.
	 *
	 * @param	array	$table
	 * @return	string
	 */
	public static function generateTable(array $table) {
		$columnSize = array();
		foreach ($table as $row) {
			$i = 0;
			foreach ($row as $column) {
				if (!isset($columnSize[$i])) $columnSize[$i] = 0;
				$columnSize[$i] = max($columnSize[$i], StringUtil::length(AnsiUtil::stripAnsi($column)));
				$i++;
			}
		}
		
		$result = '';
		$result .= '+';
		foreach ($columnSize as $column) {
			$result .= str_repeat('-', $column + 2).'+';
		}
		$result .= PHP_EOL;
		
		foreach ($table as $row) {
			$result .= "|";
			$i = 0;
			foreach ($row as $column) {
				$paddedString = StringUtil::pad(AnsiUtil::stripAnsi($column), $columnSize[$i], ' ', (is_numeric($column) ? STR_PAD_LEFT : STR_PAD_RIGHT));
				$result .= ' '.StringUtil::replace(AnsiUtil::stripAnsi($column), $column, $paddedString).' |';
				$i++;
			}
				
			$result .= PHP_EOL."+";
			foreach ($columnSize as $column) {
				$result .= str_repeat('-', $column + 2).'+';
			}
			$result .= PHP_EOL;
		}
		
		return $result;
	}
	
	private function __construct() { }
}
