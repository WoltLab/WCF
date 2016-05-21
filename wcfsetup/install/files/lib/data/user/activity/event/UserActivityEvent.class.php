<?php
namespace wcf\data\user\activity\event;
use wcf\data\DatabaseObject;

/**
 * Represents a user's activity.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.activity.event
 * @category	Community Framework
 *
 * @property-read	integer		$eventID
 * @property-read	integer		$objectTypeID
 * @property-read	integer		$objectID
 * @property-read	integer|null	$languageID
 * @property-read	integer		$userID
 * @property-read	integer		$time
 * @property-read	array		$additionalData
 */
class UserActivityEvent extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_activity_event';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'eventID';
	
	/**
	 * @inheritDoc
	 */
	public function __get($name) {
		$value = parent::__get($name);
		
		// treat additional data as data variables if it is an array
		if ($value === null && isset($this->data['additionalData'][$name])) {
			$value = $this->data['additionalData'][$name];
		}
		
		return $value;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		$this->data['additionalData'] = @unserialize($this->data['additionalData']);
		if (!is_array($this->data['additionalData'])) {
			$this->data['additionalData'] = [];
		}
	}
}
