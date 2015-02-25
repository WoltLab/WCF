<?php
namespace wcf\system\cache\builder;
use wcf\data\ad\AdList;

/**
 * Caches the enabled ads.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class AdCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$adList = new AdList();
		$adList->getConditionBuilder()->add('isDisabled = ?', array(0));
		$adList->sqlOrderBy = 'showOrder ASC';
		$adList->readObjects();
		
		$data = array();
		foreach ($adList as $ad) {
			if (!isset($data[$ad->objectTypeID])) {
				$data[$ad->objectTypeID] = array();
			}
			
			$data[$ad->objectTypeID][$ad->adID] = $ad;
		}
		
		return $data;
	}
}
