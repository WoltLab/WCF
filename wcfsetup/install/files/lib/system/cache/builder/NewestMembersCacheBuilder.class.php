<?php
namespace wcf\system\cache\builder;

/**
 * Caches a list of the newest members.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class NewestMembersCacheBuilder extends AbstractSortedUserCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected $sortField = 'registrationDate';
}
