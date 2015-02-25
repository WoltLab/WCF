<?php
namespace wcf\system\package\plugin;
use wcf\system\cache\builder\SitemapCacheBuilder;
use wcf\system\WCF;

/**
 * Installs, updates and deletes sitemaps.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class SitemapPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\sitemap\SitemapEditor';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		sitemapName = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$item['attributes']['name'],
				$this->installation->getPackageID()
			));
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$showOrder = (isset($data['elements']['showOrder'])) ? intval($data['elements']['showOrder']) : null;
		$showOrder = $this->getShowOrder($showOrder, null, 'showOrder');
		
		return array(
			'sitemapName' => $data['attributes']['name'],
			'className' => $data['elements']['classname'],
			'showOrder' => $showOrder,
			'options' => (isset($data['elements']['options'])) ? $data['elements']['options'] : '',
			'permissions' => (isset($data['elements']['permissions'])) ? $data['elements']['permissions'] : ''
		);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	sitemapName = ?
				AND packageID = ?";
		$parameters = array(
			$data['sitemapName'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::cleanup()
	 */
	protected function cleanup() {
		SitemapCacheBuilder::getInstance()->reset();
	}
}
