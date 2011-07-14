<?php
namespace wcf\system\package\plugin;
use wcf\system\cache\CacheHandler;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes event listeners.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class EventListenerPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\event\listener\EventListenerEditor';
	
	/**
	 * @see AbstractXMLPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'event_listener';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::$tagName
	 */	
	public $tagName = 'eventlistener';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		packageID = ?
					AND eventClassName = ?
					AND eventName = ?,
					AND inherit = ?
					AND listenerClassName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$this->installation->getPackageID(),
				$item['elements']['eventclassname'],
				$item['elements']['eventname'],
				$item['elements']['inherit'],
				$item['elements']['listenerclassname']
			));
		}
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		$nice = (isset($data['elements']['nice'])) ? intval($data['elements']['nice']) : 0;
		if ($nice < -128) $nice = -128;
		else if ($nice > 127) $nice = 127;
		
		return array(
			'eventClassName' => $data['elements']['eventclassname'],
			'eventName' => $data['elements']['eventname'],
			'inherit' => (isset($data['elements']['inherit'])) ? intval($data['elements']['inherit']) : 0,
			'listenerClassName' => $data['elements']['listenerclassname'],
			'niceValue' => $nice
		);
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	eventClassName = ?
				AND eventName = ?
				AND listenerClassName = ?
				AND packageID = ?";
		$parameters = array(
			$data['eventClassName'],
			$data['eventName'],
			$data['listenerClassName'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
	
	/**
	 * @see	 PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		parent::uninstall();
		
		// clear cache immediately
		CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.eventListener-*.php');
	}
}
?>
