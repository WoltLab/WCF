<?php
namespace wcf\data\user\group\assignment;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user group assignments.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.assignment
 * @category	Community Framework
 *
 * @method	UserGroupAssignment		current()
 * @method	UserGroupAssignment[]		getObjects()
 * @method	UserGroupAssignment|null	search($objectID)
 * @property	UserGroupAssignment[]		$objects
 */
class UserGroupAssignmentList extends DatabaseObjectList { }
