<?php
namespace wcf\system\benchmark;
use wcf\system\SingletonFactory;
use wcf\util\FileUtil;

/**
 * Provides functions to do a benchmark.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Benchmark
 */
class Benchmark extends SingletonFactory {
	const TYPE_SQL_QUERY = 1;
	const TYPE_OTHER = 0;
	
	/**
	 * general benchmark start time
	 * @var	float
	 */
	protected $startTime = 0;
	
	/**
	 * benchmark items
	 * @var	array
	 */
	protected $items = [];
	
	/**
	 * number of executed sql queries
	 * @var	integer
	 */
	protected $queryCount = 0;
	
	/**
	 * total sql query execution time
	 * @var	float
	 */
	protected $queryTime = 0;
	
	/**
	 * Creates a new Benchmark object.
	 */
	protected function init() {
		$this->startTime = self::getMicrotime();
	}
	
	/**
	 * Starts a benchmark.
	 * 
	 * @param	string		$text
	 * @param	integer		$type
	 * @return	integer		index
	 */
	public function start($text, $type = self::TYPE_OTHER) {
		$newIndex = count($this->items);
		$this->items[$newIndex]['text']	= $text;
		$this->items[$newIndex]['type']	= $type;
		$this->items[$newIndex]['before'] = self::getMicrotime();
		$this->items[$newIndex]['start'] = self::compareMicrotimes($this->startTime, $this->items[$newIndex]['before']);
		$this->items[$newIndex]['trace'] = (defined('DEBUG_BACKTRACE_IGNORE_ARGS')) ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) : debug_backtrace();
		return $newIndex;
	}
	
	/**
	 * Stops the benchmark with the given index. If no index is given, the
	 * latest benchmark is stoped.
	 * 
	 * @param	integer		$index
	 */
	public function stop($index = null) {
		if ($index === null) {
			$index = count($this->items) - 1;
		}
		
		$this->items[$index]['after'] = self::getMicrotime();
		$this->items[$index]['use'] = self::compareMicrotimes($this->items[$index]['before'], $this->items[$index]['after']);
		$this->items[$index]['end'] = self::compareMicrotimes($this->startTime, $this->items[$index]['after']);
		if ($this->items[$index]['type'] == self::TYPE_SQL_QUERY) {
			$this->queryCount++;
			$this->queryTime += $this->items[$index]['use'];
		}
	}
	
	/**
	 * Returns the execution time.
	 * 
	 * @return	float
	 */
	public function getExecutionTime() {
		return $this->compareMicrotimes($this->startTime, self::getMicrotime());
	}
	
	/**
	 * Returns the sql query execution time
	 * 
	 * @return	float
	 */
	public function getQueryExecutionTime() {
		return $this->queryTime;
	}
	
	/**
	 * Returns the number of executed sql queries.
	 * 
	 * @return	integer
	 */
	public function getQueryCount() {
		return $this->queryCount;
	}
	
	/**
	 * Returns the logged items.
	 * 
	 * @return	array
	 */
	public function getItems() {
		return $this->items;
	}
	
	/**
	 * Returns the current unix timestamp as a float.
	 * 
	 * @return	float		unix timestamp
	 */
	protected static function getMicrotime() {
		return microtime(true);
	}
	
	/**
	 * Calculates the difference of two unix timestamps.
	 * 
	 * @param	float		$startTime
	 * @param	float		$endTime
	 * @return	float
	 */
	protected static function compareMicrotimes($startTime, $endTime) {
		return round($endTime - $startTime, 4);
	}
	
	/**
	 * Returns the formatted peak of memory_usage.
	 * 
	 * @return	string
	 */
	public function getMemoryUsage() {
		return FileUtil::formatFilesize(memory_get_peak_usage());
	}
}
