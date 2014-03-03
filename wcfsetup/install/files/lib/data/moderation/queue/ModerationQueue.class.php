<?php
namespace wcf\data\moderation\queue;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a moderation queue entry.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.moderation.queue
 * @category	Community Framework
 */
class ModerationQueue extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'moderation_queue';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseTableIndexName = 'queueID';
	
	// states of column 'status'
	const STATUS_OUTSTANDING = 0;
	const STATUS_PROCESSING = 1;
	const STATUS_DONE = 2;
	
	/**
	 * @see	\wcf\data\IStorableObject::__get()
	 */
	public function __get($name) {
		$value = parent::__get($name);
		
		// treat additional data as data variables if it is an array
		if ($value === null) {
			if (is_array($this->data['additionalData']) && isset($this->data['additionalData'][$name])) {
				$value = $this->data['additionalData'][$name];
			}
		}
		
		return $value;
	}
	
	/**
	 * @see	\wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		$this->data['additionalData'] = @unserialize($this->data['additionalData']);
		if (!is_array($this->data['additionalData'])) {
			$this->data['additionalData'] = array();
		}
	}
	
	/**
	 * Returns true if current user can edit this moderation queue.
	 * 
	 * @return	boolean
	 */
	public function canEdit() {
		$sql = "SELECT	isAffected
			FROM	wcf".WCF_N."_moderation_queue_to_user
			WHERE	queueID = ?
				AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->queueID,
			WCF::getUser()->userID
		));
		$row = $statement->fetchArray();
		
		return ($row !== false && $row['isAffected']);
	}
	
	/**
	 * Returns true, if this queue is done.
	 * 
	 * @return	boolean
	 */
	public function isDone() {
		return ($this->status == self::STATUS_DONE);
	}
}
