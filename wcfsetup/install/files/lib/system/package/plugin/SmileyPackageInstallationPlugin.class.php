<?php
namespace wcf\system\package\plugin;
use wcf\system\WCF;

/**
 * Installs, updates and deletes smilies.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category	Community Framework
 */
class SmileyPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = 'wcf\data\smiley\SmileyEditor';
	
	/**
	 * @inheritDoc
	 */
	public $tableName = 'smiley';
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'smiley';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		smileyCode = ?
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
			'smileyCode' => $data['attributes']['name'],
			'smileyTitle' => $data['elements']['title'],
			'smileyPath' => $data['elements']['path'],
			'aliases' => (isset($data['elements']['aliases']) ? $data['elements']['aliases'] : '')
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	smileyCode = ?
				AND packageID = ?";
		$parameters = [
			$data['smileyCode'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
}
