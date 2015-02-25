<?php
namespace wcf\data\session\virtual;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit virtual sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session.virtual
 * @category	Community Framework
 */
class SessionVirtualEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\session\virtual\SessionVirtual';
	
	/**
	 * Updates last activity time of this virtual session.
	 */
	public function updateLastActivityTime() {
		$this->update(array(
			'lastActivityTime' => TIME_NOW
		));
	}
	
	/**
	 * Deletes the expired virtual sessions.
	 * 
	 * @param	integer		$timestamp
	 */
	public static function deleteExpiredSessions($timestamp) {
		$sql = "DELETE FROM	".call_user_func(array(static::$baseClass, 'getDatabaseTableName'))."
			WHERE		lastActivityTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($timestamp));
	}
}
