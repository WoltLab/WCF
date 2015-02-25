<?php
namespace wcf\data\user\object\watch;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit watched objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.object.watch
 * @category	Community Framework
 */
class UserObjectWatchEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\object\watch\UserObjectWatch';
}
