<?php
namespace wcf\data\cronjob;
use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\util\CronjobUtil;

/**
 * Represents a cronjob.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Cronjob
 *
 * @property-read	integer		$cronjobID
 * @property-read	string		$className
 * @property-read	integer		$packageID
 * @property-read	string		$cronjobName
 * @property-read	string		$description
 * @property-read	string		$startMinute
 * @property-read	string		$startHour
 * @property-read	string		$startDom
 * @property-read	string		$startMonth
 * @property-read	string		$startDow
 * @property-read	integer		$lastExec
 * @property-read	integer		$nextExec
 * @property-read	integer		$afterNextExec
 * @property-read	integer		$isDisabled
 * @property-read	integer		$canBeEdited
 * @property-read	integer		$canBeDisabled
 * @property-read	integer		$state
 * @property-read	integer		$failCount
 * @property-read	string		$options
 */
class Cronjob extends DatabaseObject {
	use TDatabaseObjectOptions;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'cronjob';
	
	/**
	 * @inheritDoc
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
	 * prefix of automatically created cronjob names
	 * @var	string
	 * @deprecated	will be removed once cronjob names are mandatory
	 */
	const AUTOMATIC_NAME_PREFIX = 'com.woltlab.wcf.cronjob';
	
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
