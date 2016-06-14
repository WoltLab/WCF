<?php
namespace wcf\util;
use phpline\internal\AnsiUtil;
use wcf\system\CLIWCF;

/**
 * Provide convenience methods for use on command line interface.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class CLIUtil {
	/**
	 * Generates a table.
	 * 
	 * @param	array		$table
	 * @return	string
	 */
	public static function generateTable(array $table) {
		$columnSize = [];
		foreach ($table as $row) {
			$i = 0;
			foreach ($row as $column) {
				if (!isset($columnSize[$i])) $columnSize[$i] = 0;
				$columnSize[$i] = max($columnSize[$i], mb_strlen(AnsiUtil::stripAnsi($column)));
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
				$result .= ' '.str_replace(AnsiUtil::stripAnsi($column), $column, $paddedString).' |';
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
	
	/**
	 * Generates a list.
	 * 
	 * @param	array		$list
	 * @return	string
	 */
	public static function generateList(array $list) {
		$result = '';
		foreach ($list as $row) {
			$parts = StringUtil::split($row, CLIWCF::getTerminal()->getWidth() - 2);
			$result .= '* '.implode(PHP_EOL.'  ', $parts).PHP_EOL;
		}
		
		return $result;
	}
	
	/**
	 * Formats time.
	 * 
	 * @param	integer	$timestamp
	 * @return	string
	 */
	public static function formatTime($timestamp) {
		$dateTimeObject = DateUtil::getDateTimeByTimestamp($timestamp);
		$date = DateUtil::format($dateTimeObject, DateUtil::DATE_FORMAT);
		$time = DateUtil::format($dateTimeObject, DateUtil::TIME_FORMAT);
		
		return str_replace('%time%', $time, str_replace('%date%', $date, CLIWCF::getLanguage()->get('wcf.date.dateTimeFormat')));
	}
	
	/**
	 * Formats dates.
	 * 
	 * @param	integer	$timestamp
	 * @return	string
	 */
	public static function formatDate($timestamp) {
		return DateUtil::format(DateUtil::getDateTimeByTimestamp($timestamp), DateUtil::DATE_FORMAT);
	}
	
	/**
	 * Forbid creation of CLIUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
