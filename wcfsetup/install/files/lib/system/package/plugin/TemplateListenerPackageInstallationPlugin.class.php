<?php
namespace wcf\system\package\plugin;
use wcf\system\cache\CacheHandler;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes template listeners.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class TemplateListenerPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\template\listener\TemplateListenerEditor';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
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
			$statement->execute(array(
				$this->installation->getPackageID(),
				$item['elements']['environment'],
				$item['elements']['eventname'],
				$item['attributes']['name'],
				$item['elements']['templatename']
			));
		}
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		return array(
			'environment' => $data['elements']['environment'],
			'eventName' => $data['elements']['eventname'],
			'name' => $data['attributes']['name'],
			'templateCode' => $data['elements']['templatecode'],
			'templateName' => $data['elements']['templatename']
		);
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ?
				AND name = ?
				AND templateName = ?
				AND eventName = ?
				AND environment = ?";
		$parameters = array(
			$this->installation->getPackageID(),
			$data['name'],
			$data['templateName'],
			$data['eventName'],
			$data['environment']
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::cleanup()
	 */	
	protected function cleanup() {
		// clear cache immediately
		CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.templateListener-*.php');
		CacheHandler::getInstance()->clear(WCF_DIR.'cache/templateListener', '*.php');
	}
}
