<?php
namespace wcf\data\cronjob;
use wcf\data\DatabaseObject;
use wcf\util\CronjobUtil;
use wcf\system\WCF;

/**
 * Represents a cronjob.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cronjob
 * @category 	Community Framework
 */
class Cronjob extends DatabaseObject {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'cronjob';
	
	/**
	 * @see	DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'cronjobID';
	
	/**
	 * Cronjob is available for execution.
	 */
	const READY = 0;
	
	/**
	 * Cronjob is currently processed, preventing multiple execution.
	 */
	const PENDING = 1;
	
	/**
	 * Cronjob is being executed.
	 */
	const EXECUTING = 2;
	
	/**
	 * Returns timestamp of next execution.
	 * 
	 * @param	integer		$timeBase
	 * @return	integer
	 */
	public function getNextExec($timeBase = null) {
		if ($timeBase === null) {
			if ($this->lastExec) {
				$timeBase = $this->lastExec;
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
		return (WCF::getSession()->getPermission('admin.system.cronjobs.canEditCronjob') && $this->canBeEdited);
	}
	
	/**
	 * Returns true if current user may delete this cronjob.
	 * 
	 * @return	boolean
	 */
	public function isDeletable() {
		return (WCF::getSession()->getPermission('admin.system.cronjobs.canDeleteCronjob') && $this->canBeEdited);
	}
	
	/**
	 * Returns true if current user may enable or disable this cronjob.
	 * 
	 * @return	true
	 */
	public function canBeDisabled() {
		return (WCF::getSession()->getPermission('admin.system.cronjobs.canEnableDisableCronjob') && $this->canBeDisabled);
	}
}
?>
