<?php
namespace wcf\data\user\rank;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user rank-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Rank
 * 
 * @method	UserRank		create()
 * @method	UserRankEditor[]	getObjects()
 * @method	UserRankEditor		getSingleObject()
 */
class UserRankAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.user.rank.canManageRank'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['delete'];
}
