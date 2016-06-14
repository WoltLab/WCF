<?php
namespace wcf\system\package\plugin;
use wcf\data\object\type\definition\ObjectTypeDefinitionEditor;
use wcf\system\WCF;

/**
 * Installs, updates and deletes object type definitions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 */
class ObjectTypeDefinitionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = ObjectTypeDefinitionEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'definition';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		definitionName = ?
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
			'interfaceName' => (isset($data['elements']['interfacename']) ? $data['elements']['interfacename'] : ''),
			'definitionName' => $data['elements']['name'],
			'categoryName' => (isset($data['elements']['categoryname']) ? $data['elements']['categoryname'] : '')
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	definitionName = ?";
		$parameters = [$data['definitionName']];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
}
