<?php
namespace wcf\data\user\profile\menu\item;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user profile menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.profile.menu.item
 * @category	Community Framework
 *
 * @method	UserProfileMenuItem		current()
 * @method	UserProfileMenuItem[]		getObjects()
 * @method	UserProfileMenuItem|null	search($objectID)
 * @property	UserProfileMenuItem[]		$objects
 */
class UserProfileMenuItemList extends DatabaseObjectList { }
