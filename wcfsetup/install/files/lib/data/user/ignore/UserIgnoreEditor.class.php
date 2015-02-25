<?php
namespace wcf\data\user\ignore;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ignored users.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.ignore
 * @category	Community Framework
 */
class UserIgnoreEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\ignore\UserIgnore';
}
