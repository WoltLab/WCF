<?php
namespace wcf\system\cache\builder;
use wcf\data\user\group\assignment\UserGroupAssignmentList;

/**
 * Caches the enabled automatic user group assignments.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class UserGroupAssignmentCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$assignmentList = new UserGroupAssignmentList();
		$assignmentList->getConditionBuilder()->add('isDisabled = ?', array(0));
		$assignmentList->readObjects();
		
		return $assignmentList->getObjects();
	}
}
