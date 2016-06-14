<?php
namespace wcf\system\package\plugin;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\clipboard\action\ClipboardActionEditor;
use wcf\system\WCF;

/**
 * Installs, updates and deletes clipboard actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 */
class ClipboardActionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = ClipboardActionEditor::class;
	
	/**
	 * list of pages per action id
	 * @var	mixed[][]
	 */
	protected $pages = [];
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'action';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		actionName = ?
					AND actionClassName = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['name'],
				$item['elements']['actionclassname'],
				$this->installation->getPackageID()
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element) {
		$nodeValue = $element->nodeValue;
		
		// read pages
		if ($element->tagName == 'pages') {
			$nodeValue = [];
			
			$pages = $xpath->query('child::ns:page', $element);
			foreach ($pages as $page) {
				$nodeValue[] = $page->nodeValue;
			}
		}
		
		$elements[$element->tagName] = $nodeValue;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$showOrder = (isset($data['elements']['showorder'])) ? intval($data['elements']['showorder']) : null;
		$showOrder = $this->getShowOrder($showOrder, $data['elements']['actionclassname'], 'actionClassName');
		
		return [
			'actionClassName' => $data['elements']['actionclassname'],
			'actionName' => $data['attributes']['name'],
			'pages' => $data['elements']['pages'],
			'showOrder' => $showOrder
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	actionName = ?
				AND actionClassName = ?
				AND packageID = ?";
		$parameters = [
			$data['actionName'],
			$data['actionClassName'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function import(array $row, array $data) {
		// extract pages
		$pages = $data['pages'];
		unset($data['pages']);
		
		/** @var ClipboardAction $action */
		$action = parent::import($row, $data);
		
		// store pages for later import
		$this->pages[$action->actionID] = $pages;
		
		return $action;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function postImport() {
		// clear pages
		$sql = "DELETE FROM	wcf".WCF_N."_clipboard_page
			WHERE		packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->installation->getPackageID()]);
		
		if (!empty($this->pages)) {
			// insert pages
			$sql = "INSERT INTO	wcf".WCF_N."_clipboard_page
						(pageClassName, packageID, actionID)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($this->pages as $actionID => $pages) {
				foreach ($pages as $pageClassName) {
					$statement->execute([
						$pageClassName,
						$this->installation->getPackageID(),
						$actionID
					]);
				}
			}
		}
	}
}
