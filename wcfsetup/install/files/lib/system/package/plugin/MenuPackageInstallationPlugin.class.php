<?php
namespace wcf\system\package\plugin;
use wcf\data\menu\Menu;
use wcf\data\menu\MenuEditor;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Installs, updates and deletes menus.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category	Community Framework
 * @since	2.2
 */
class MenuPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = MenuEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'menu';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM     wcf".WCF_N."_menu
			WHERE           identifier = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['identifier'],
				$this->installation->getPackageID()
			]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * @inheritDoc
	 * @throws      SystemException
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		if ($element->tagName === 'title') {
			if (empty($element->getAttribute('language'))) {
				throw new SystemException("Missing required attribute 'language' for menu '" . $element->parentNode->getAttribute('identifier') . "'");
			}
			
			// <title> can occur multiple times using the `language` attribute
			if (!isset($elements['title'])) $elements['title'] = [];
			
			$elements['title'][$element->getAttribute('language')] = $element->nodeValue;
		}
		else {
			$elements[$element->tagName] = $nodeValue;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		return [
			'identifier' => $data['attributes']['identifier'],
			'title' => $this->getI18nValues($data['elements']['title']),
			'originIsSystem' => 1
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_menu
			WHERE	identifier = ?
				AND packageID = ?";
		$parameters = array(
			$data['identifier'],
			$this->installation->getPackageID()
		);
		
		return array(
			'sql' => $sql,
			'parameters' => $parameters
		);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		// updating menus is not supported because the only modifiable data is the
		// title and overwriting it could conflict with user changes
		if (!empty($row)) {
			return new Menu(null, $row);
		}
		
		return parent::import($row, $data);
	}
}
