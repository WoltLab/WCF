<?php
namespace wcf\system\cache\builder;
use wcf\data\trophy\TrophyList;

/**
 * Caches the trophies.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 * @since	3.1
 */
class TrophyCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$trophyList = new TrophyList();
		
		if (isset($parameters['onlyEnabled']) && $parameters['onlyEnabled']) {
			$trophyList->getConditionBuilder()->add('isDisabled = ?', [0]);
		}
		
		$trophyList->readObjects();
		return $trophyList->getObjects();
	}
}
