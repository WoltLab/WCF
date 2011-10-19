<?php
namespace wcf\system\package\plugin;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * This PIP installs, updates or deletes object types.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class ObjectTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\object\type\ObjectTypeEditor';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'object_type';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */	
	public $tagName = 'type';
	
	/**
	 * Gets the definition id by name
	 * 
	 * @param	string		$definitionName
	 * @return	integer
	 */
	protected function getDefinitionID($definitionName) {
		// get object type id
		$sql = "SELECT		notification_object_type.definitionID
			FROM		wcf".WCF_N."_package_dependency package_dependency,
					wcf".WCF_N."_object_type_definition object_type_definition
			WHERE		object_type_definition.packageID = package_dependency.dependency
					AND package_dependency.packageID = ?
					AND object_type_definition.definitionName = ?
			ORDER BY	package_dependency.priority DESC";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute(array($this->installation->getPackageID(), $definitionName));
		$row = $statement->fetchArray();
		if (empty($row['definitionID'])) throw new SystemException("unknown object type definition '".$definitionName."' given");
		return $row['definitionID'];
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		objectType = ?
					definitionID = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute(array(
				$item['attributes']['name'],
				$this->getDefinitionID($data['elements']['definitionname']),
				$this->installation->getPackageID()
			));
		}
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) {
		return array(
			'definitionID' => $this->getDefinitionID($data['elements']['definitionname']),
			'objectType' => $data['attributes']['name'],
			'className' => (isset($data['elements']['classname']) ? $data['elements']['classname'] : ''),
			'additionalData' => serialize(isset($data['elements']['additionaldata']) ? $data['elements']['additionaldata'] : array())
		);
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	objectType = ?
				definitionID = ?
				AND packageID = ?";
		$parameters = array(
			$data['objectType'],
			$data['definitionID'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
}
