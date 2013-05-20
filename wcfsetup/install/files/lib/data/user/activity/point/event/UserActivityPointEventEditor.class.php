<?php
namespace wcf\data\user\activity\point\event;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user activity point events.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	data.user.activity.point.event
 * @category	Community Framework
 */
class UserActivityPointEventEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\activity\point\event\UserActivityPointEvent';
}
