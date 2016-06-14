<?php
namespace wcf\system\cache\builder;
use wcf\data\user\group\assignment\UserGroupAssignmentList;

/**
 * Caches the enabled automatic user group assignments.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class UserGroupAssignmentCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$assignmentList = new UserGroupAssignmentList();
		$assignmentList->getConditionBuilder()->add('isDisabled = ?', [0]);
		$assignmentList->readObjects();
		
		return $assignmentList->getObjects();
	}
}
