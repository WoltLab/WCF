<?php
namespace wcf\system\package\plugin;
use wcf\data\template\listener\TemplateListenerEditor;
use wcf\system\cache\builder\TemplateListenerCodeCacheBuilder;
use wcf\system\WCF;

/**
 * Installs, updates and deletes template listeners.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class TemplateListenerPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = TemplateListenerEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND environment = ?
					AND eventName = ?
					AND name = ?
					AND templateName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$this->installation->getPackageID(),
				$item['elements']['environment'],
				$item['elements']['eventname'],
				$item['attributes']['name'],
				$item['elements']['templatename']
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$niceValue = isset($data['elements']['nice']) ? intval($data['elements']['nice']) : 0;
		if ($niceValue < -128) {
			$niceValue = -128;
		}
		else if ($niceValue > 127) {
			$niceValue = 127;
		}
		
		return [
			'environment' => $data['elements']['environment'],
			'eventName' => $data['elements']['eventname'],
			'niceValue' => $niceValue,
			'name' => $data['attributes']['name'],
			'options' => (isset($data['elements']['options']) ? $data['elements']['options'] : ''),
			'permissions' => (isset($data['elements']['permissions']) ? $data['elements']['permissions'] : ''),
			'templateCode' => $data['elements']['templatecode'],
			'templateName' => $data['elements']['templatename']
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ?
				AND name = ?
				AND templateName = ?
				AND eventName = ?
				AND environment = ?";
		$parameters = [
			$this->installation->getPackageID(),
			$data['name'],
			$data['templateName'],
			$data['eventName'],
			$data['environment']
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
		// clear cache immediately
		TemplateListenerCodeCacheBuilder::getInstance()->reset();
	}
}
