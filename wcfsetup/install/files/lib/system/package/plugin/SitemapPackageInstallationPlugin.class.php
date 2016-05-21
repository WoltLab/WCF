<?php
namespace wcf\system\package\plugin;
use wcf\data\sitemap\SitemapEditor;
use wcf\system\cache\builder\SitemapCacheBuilder;
use wcf\system\WCF;

/**
 * Installs, updates and deletes sitemaps.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class SitemapPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = SitemapEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		sitemapName = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['name'],
				$this->installation->getPackageID()
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$showOrder = (isset($data['elements']['showOrder'])) ? intval($data['elements']['showOrder']) : null;
		$showOrder = $this->getShowOrder($showOrder, null, 'showOrder');
		
		return [
			'sitemapName' => $data['attributes']['name'],
			'className' => $data['elements']['classname'],
			'showOrder' => $showOrder,
			'options' => (isset($data['elements']['options'])) ? $data['elements']['options'] : '',
			'permissions' => (isset($data['elements']['permissions'])) ? $data['elements']['permissions'] : ''
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	sitemapName = ?
				AND packageID = ?";
		$parameters = [
			$data['sitemapName'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function cleanup() {
		SitemapCacheBuilder::getInstance()->reset();
	}
}
