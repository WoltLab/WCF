<?php
namespace wcf\system\cache\builder;
use wcf\data\user\UserList;

/**
 * Caches a list of the newest members.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 * @since	3.0
 */
abstract class AbstractSortedUserCacheBuilder extends AbstractCacheBuilder {
	/**
	 * default limit value if no limit parameter is provided
	 * @var	integer
	 */
	protected $defaultLimit = 5;
	
	/**
	 * default sort order if no sort order parameter is provided
	 * @var	string
	 */
	protected $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	protected $maxLifetime = 300;
	
	/**
	 * if `true`, only positive values of the database column will be considered
	 * @var	boolean
	 */
	protected $positiveValuesOnly = false;
	
	/**
	 * database table column used for sorting
	 * @var	string
	 */
	protected $sortField;
	
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$sortOrder = $this->defaultSortOrder;
		if (!empty($parameters['sortOrder'])) {
			$sortOrder = $parameters['sortOrder'];
		}
		
		$userProfileList = new UserList();
		if ($this->positiveValuesOnly) {
			$userProfileList->getConditionBuilder()->add('user_table.'.$this->sortField.' > ?', [0]);
		}
		$userProfileList->sqlOrderBy = 'user_table.'.$this->sortField.' '.$sortOrder;
		$userProfileList->sqlLimit = !empty($parameters['limit']) ? $parameters['limit'] : $this->defaultLimit;
		$userProfileList->readObjectIDs();
		
		return $userProfileList->getObjectIDs();
	}
}
