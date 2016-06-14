<?php
namespace wcf\system\package\plugin;
use wcf\data\package\installation\plugin\PackageInstallationPluginEditor;
use wcf\system\WCF;

/**
 * Installs, updates and deletes package installation plugins.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class PIPPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = PackageInstallationPluginEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'pip';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		pluginName = ?
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
		return [
			'className' => $data['nodeValue'],
			'pluginName' => $data['attributes']['name'],
			'priority' => ($this->installation->getPackage()->package == 'com.woltlab.wcf' ? 1 : 0)
		];
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
	 * @since	3.0
	 */
	public static function getDefaultFilename() {
		return 'packageInstallationPlugin.xml';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	pluginName = ?
				AND packageID = ?";
		$parameters = [
			$data['pluginName'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
}
