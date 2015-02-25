<?php
namespace wcf\data\cronjob;
use wcf\data\DatabaseObject;
use wcf\util\CronjobUtil;

/**
 * Represents a cronjob.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cronjob
 * @category	Community Framework
 */
class Cronjob extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'cronjob';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'cronjobID';
	
	/**
	 * indicates that cronjob is available for execution
	 */
	const READY = 0;
	
	/**
	 * indicates that cronjob is currently processed, preventing multiple execution
	 */
	const PENDING = 1;
	
	/**
	 * indicates that cronjob is executed at the moment
	 */
	const EXECUTING = 2;
	
	/**
	 * maximum number of allowed fails
	 */
	const MAX_FAIL_COUNT = 3;
	
	/**
	 * Returns timestamp of next execution.
	 * 
	 * @param	integer		$timeBase
	 * @return	integer
	 */
	public function getNextExec($timeBase = null) {
		if ($timeBase === null) {
			if ($this->lastExec) {
				$timeBase = $this->lastExec + 120;
				if ($timeBase < TIME_NOW) {
					$timeBase = TIME_NOW + 120;
				}
			}
			else {
				// first time setup
				$timeBase = TIME_NOW;
			}
		}
		
		$nextExec = CronjobUtil::calculateNextExec(
			$this->startMinute,
			$this->startHour,
			$this->startDom,
			$this->startMonth,
			$this->startDow,
			$timeBase
		);
		
		return $nextExec;
	}
	
	/**
	 * Returns true if current user may edit this cronjob.
	 * 
	 * @return	boolean
	 */
	public function isEditable() {
		return $this->canBeEdited;
	}
	
	/**
	 * Returns true if current user may delete this cronjob.
	 * 
	 * @return	boolean
	 */
	public function isDeletable() {
		return $this->canBeEdited && $this->canBeDisabled;
	}
	
	/**
	 * Returns true if current user may enable or disable this cronjob.
	 * 
	 * @return	boolean
	 */
	public function canBeDisabled() {
		return $this->canBeDisabled;
	}
}
