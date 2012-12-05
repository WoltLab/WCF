<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches the structure of templates.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class TemplateCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$information = explode('-', $cacheResource['cache']);
		if (count($information) == 3) {
			$prefix = $information[0].'_';
		}
		else {
			$prefix = '';
		}
		
		$data = array();
		
		// get all templates and filter options with low priority
		$sql = "SELECT		templateName, template.packageID 
			FROM		wcf".WCF_N."_".$prefix."template";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if (!isset($data[$row['templateName']])) {
				$data[$row['templateName']] = $row['packageID'];
			}
		}
		
		return $data;
	}
}
