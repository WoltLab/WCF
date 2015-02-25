<?php
namespace wcf\data\user\follow;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit followers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.follow
 * @category	Community Framework
 */
class UserFollowEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\follow\UserFollow';
}
