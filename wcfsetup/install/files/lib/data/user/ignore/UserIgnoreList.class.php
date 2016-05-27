<?php
namespace wcf\data\user\ignore;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ignored users.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.ignore
 * @category	Community Framework
 *
 * @method	UserIgnore		current()
 * @method	UserIgnore[]		getObjects()
 * @method	UserIgnore|null		search($objectID)
 * @property	UserIgnore[]		$objects
 */
class UserIgnoreList extends DatabaseObjectList { }
