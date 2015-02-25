<?php
namespace wcf\system\package\plugin;
use wcf\system\cache\builder\EventListenerCacheBuilder;
use wcf\system\WCF;

/**
 * Installs, updates and deletes event listeners.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
 */
class EventListenerPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\event\listener\EventListenerEditor';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */
	public $tagName = 'eventlistener';
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND environment = ?
					AND eventClassName = ?
					AND eventName = ?
					AND inherit = ?
					AND listenerClassName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$this->installation->getPackageID(),
				(isset($item['elements']['environment']) ? $item['elements']['environment'] : 'user'),
				$item['elements']['eventclassname'],
				$item['elements']['eventname'],
				$item['elements']['inherit'],
				$item['elements']['listenerclassname']
			));
		}
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$nice = (isset($data['elements']['nice'])) ? intval($data['elements']['nice']) : 0;
		if ($nice < -128) $nice = -128;
		else if ($nice > 127) $nice = 127;
		
		return array(
			'environment' => (isset($data['elements']['environment']) ? $data['elements']['environment'] : 'user'),
			'eventClassName' => $data['elements']['eventclassname'],
			'eventName' => $data['elements']['eventname'],
			'inherit' => (isset($data['elements']['inherit'])) ? intval($data['elements']['inherit']) : 0,
			'listenerClassName' => $data['elements']['listenerclassname'],
			'niceValue' => $nice
		);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	packageID = ?
				AND environment = ?
				AND eventClassName = ?
				AND eventName = ?
				AND listenerClassName = ?";
		$parameters = array(
			$this->installation->getPackageID(),
			$data['environment'],
			$data['eventClassName'],
			$data['eventName'],
			$data['listenerClassName']
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		parent::uninstall();
		
		// clear cache immediately
		EventListenerCacheBuilder::getInstance()->reset();
	}
}
