<?php
namespace wcf\data\user\follow;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit followers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.follow
 * @category	Community Framework
 * 
 * @method	UserFollow	getDecoratedObject()
 * @mixin	UserFollow
 */
class UserFollowEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserFollow::class;
}
