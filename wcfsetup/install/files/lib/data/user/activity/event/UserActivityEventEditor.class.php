<?php
namespace wcf\data\user\activity\event;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user activity events.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.activity.event
 * @category	Community Framework
 * 
 * @method	UserActivityEvent	getDecoratedObject()
 * @mixin	UserActivityEvent
 */
class UserActivityEventEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserActivityEvent::class;
}
