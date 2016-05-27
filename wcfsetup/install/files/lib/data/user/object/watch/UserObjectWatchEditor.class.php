<?php
namespace wcf\data\user\object\watch;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit watched objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.object.watch
 * @category	Community Framework
 * 
 * @method	UserObjectWatch		getDecoratedObject()
 * @mixin	UserObjectWatch
 */
class UserObjectWatchEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserObjectWatch::class;
}
