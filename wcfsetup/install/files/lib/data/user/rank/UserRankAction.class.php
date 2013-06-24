<?php
namespace wcf\data\user\rank;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user rank-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	data.user.rank
 * @category	Community Framework
 */
class UserRankAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.user.rank.canManageRank');
}
