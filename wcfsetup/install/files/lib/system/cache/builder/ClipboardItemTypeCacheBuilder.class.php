<?php
namespace wcf\system\cache\builder;
use wcf\data\clipboard\item\type\ClipboardItemTypeList;
use wcf\system\package\PackageDependencyHandler;

/**
 * Caches clipboard item types.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class ClipboardItemTypeCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$typeList = new ClipboardItemTypeList();
		$typeList->getConditionBuilder()->add("clipboard_item_type.packageID IN (?)", array(PackageDependencyHandler::getDependencies()));
		$typeList->sqlLimit = 0;
		$typeList->readObjects();
		
		$data = array(
			'typeNames' => array(),
			'types' => array()
		);
		foreach ($typeList->getObjects() as $typeID => $type) {
			$data['typeNames'][$type->typeName] = $typeID;
			$data['types'][$typeID] = $type;
		}
		
		return $data;
	}
}
