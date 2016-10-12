<?php
namespace wcf\system\cache\builder;
use wcf\data\ad\AdList;

/**
 * Caches the enabled ads.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class AdCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$adList = new AdList();
		$adList->getConditionBuilder()->add('isDisabled = ?', [0]);
		$adList->sqlOrderBy = 'showOrder ASC';
		$adList->readObjects();
		
		$data = [];
		foreach ($adList as $ad) {
			if (!isset($data[$ad->objectTypeID])) {
				$data[$ad->objectTypeID] = [];
			}
			
			$data[$ad->objectTypeID][$ad->adID] = $ad;
		}
		
		return $data;
	}
}
