<?php
namespace wcf\system\package\plugin;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes page locations.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class PageLocationPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\page\location\PageLocationEditor';
	
	/**
	 * @see AbstractXMLPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'page_location';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::$tagName
	 */	
	public $tagName = 'pagelocation';
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		locationName = ?
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
	 * @see	AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		return array(
			'classPath' => (isset($data['elements']['classpath'])) ? $data['elements']['classpath'] : '',
			'locationName' => $data['attributes']['name'],
			'locationPattern' => $data['elements']['pattern']
		);
	}
	
	/**
	 * @see	AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	locationName = ?
				AND packageID = ?";
		$parameters = array(
			$data['locationName'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
}
