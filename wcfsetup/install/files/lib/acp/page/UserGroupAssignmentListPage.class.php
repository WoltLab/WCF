<?php
namespace wcf\acp\page;
use wcf\data\user\group\assignment\UserGroupAssignmentList;
use wcf\page\MultipleLinkPage;

/**
 * Lists the available automatic user group assignments.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	UserGroupAssignmentList		$objectList
 */
class UserGroupAssignmentListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.group.assignment';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canManageGroupAssignment'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = UserGroupAssignmentList::class;
}
