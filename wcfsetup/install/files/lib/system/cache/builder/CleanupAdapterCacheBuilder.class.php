<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches cleanup adapters.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class CleanupAdapterCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$data = array(
			'adapters' => array(),
			'objectTypes' => array(),
			'packageIDs' => array()
		);
		
		$sql = "SELECT		listener.*, package.packageDir
			FROM		wcf".WCF_N."_cleanup_listener listener
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = listener.packageID)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if (!is_array($data['adapters'][$row['objectType']])) $data['adapters'][$row['objectType']] = array();
			$data['adapters'][$row['objectType']][] = $row;
			
			if (!is_array($data['objectTypes'][$row['objectType']])) $data['objectTypes'][$row['objectType']] = array();
			$data['objectTypes'][$row['objectType']][] = $row['packageID'];
		}
		
		$data['objectTypes'] = array_unique($data['objectTypes']);
		$data['packageIDs'] = array_unique($data['packageIDs']);
		
		return $data;
	}
}
