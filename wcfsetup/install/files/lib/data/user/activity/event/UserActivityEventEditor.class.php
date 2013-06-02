<?php
namespace wcf\data\user\activity\event;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user activity events.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	data.user.activity.event
 * @category	Community Framework
 */
class UserActivityEventEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\activity\event\UserActivityEvent';
}
