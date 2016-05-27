<?php
namespace wcf\data\acp\session\virtual;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit virtual sessions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.virtual
 * @category	Community Framework
 * 
 * @method	ACPSessionVirtual	getDecoratedObject()
 * @mixin	ACPSessionVirtual
 */
class ACPSessionVirtualEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ACPSessionVirtual::class;
	
	/**
	 * Updates last activity time of this virtual session.
	 */
	public function updateLastActivityTime() {
		$this->update(['lastActivityTime' => TIME_NOW]);
	}
	
	/**
	 * Deletes the expired virtual sessions.
	 * 
	 * @param	integer		$timestamp
	 */
	public static function deleteExpiredSessions($timestamp) {
		$sql = "DELETE FROM	".call_user_func([static::$baseClass, 'getDatabaseTableName'])."
			WHERE		lastActivityTime < ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$timestamp]);
	}
}
