<?php
namespace wcf\system\package\plugin;
use wcf\data\acp\menu\item\ACPMenuItem;
use wcf\data\DatabaseObjectList;
use wcf\page\IPage;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\OptionFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\Url;

/**
 * Abstract implementation of a package installation plugin for menu items.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
abstract class AbstractMenuPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IIdempotentPackageInstallationPlugin {
	// we do no implement `IGuiPackageInstallationPlugin` but instead just
	// provide the default implementation to ensure backwards compatibility
	// with third-party packages containing classes that extend this abstract
	// class
	use TXmlGuiPackageInstallationPlugin;
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE		menuItem = ?
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
		// adjust show order
		$showOrder = isset($data['elements']['showorder']) ? $data['elements']['showorder'] : null;
		$parent = isset($data['elements']['parent']) ? $data['elements']['parent'] : '';
		$showOrder = $this->getShowOrder($showOrder, $parent, 'parentMenuItem');
		
		// merge values and default values
		return [
			'menuItem' => $data['attributes']['name'],
			'menuItemController' => isset($data['elements']['controller']) ? $data['elements']['controller'] : '',
			'menuItemLink' => isset($data['elements']['link']) ? $data['elements']['link'] : '',
			'options' => isset($data['elements']['options']) ? StringUtil::normalizeCsv($data['elements']['options']) : '',
			'parentMenuItem' => isset($data['elements']['parent']) ? $data['elements']['parent'] : '',
			'permissions' => isset($data['elements']['permissions']) ? StringUtil::normalizeCsv($data['elements']['permissions']) : '',
			'showOrder' => $showOrder
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateImport(array $data) {
		if (empty($data['parentMenuItem'])) {
			return;
		}
		
		$sql = "SELECT	COUNT(menuItemID)
			FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE	menuItem = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$data['parentMenuItem']]);
		
		if (!$statement->fetchSingleColumn()) {
			throw new SystemException("Unable to find parent 'menu item' with name '".$data['parentMenuItem']."' for 'menu item' with name '".$data['menuItem']."'.");
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE	menuItem = ?
				AND packageID = ?";
		$parameters = [
			$data['menuItem'],
			$this->installation->getPackageID()
		];
		
		return [
			'sql' => $sql,
			'parameters' => $parameters
		];
	}
	
	/**
	 * @inheritDoc
	 * @since	3.1
	 */
	public static function getSyncDependencies() {
		return [];
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function addFormFields(IFormDocument $form) {
		/** @var IFormContainer $dataContainer */
		$dataContainer = $form->getNodeById('data');
		
		$dataContainer->appendChildren([
			TextFormField::create('menuItem')
				->objectProperty('name')
				->label('wcf.acp.pip.abstractMenu.menuItem')
				->addValidator(new FormFieldValidator('uniqueness', function(TextFormField $formField) {
					if (
						$formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE ||
						$this->editedEntry->getAttribute('name') !== $formField->getValue()
					) {
						// replace `Editor` with `List`
						$listClassName = substr($this->className, 0, -6) . 'List';
						
						/** @var DatabaseObjectList $menuItemList */
						$menuItemList = new $listClassName();
						$menuItemList->getConditionBuilder()->add('menuItem = ?', [$formField->getValue()]);
						
						if ($menuItemList->countObjects() > 0) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'notUnique',
									'wcf.acp.pip.abstractMenu.menuItem.error.notUnique'
								)
							);
						}
					}
				})),
			
			SingleSelectionFormField::create('parentMenuItem')
				->objectProperty('parent')
				->label('wcf.acp.pip.abstractMenu.parentMenuItem')
				->filterable()
				->options(function() {
					$menuStructure = $this->getMenuStructureData()['structure'];
					
					$options = [[
						'depth' => 0,
						'label' => 'wcf.global.noSelection',
						'value' => ''
					]];
					
					$buildOptions = function($parent = '', $depth = 0) use ($menuStructure, &$buildOptions) {
						// only consider menu items until the third level (thus only parent
						// menu items until the second level) as potential parent menu items
						if ($depth > 2) {
							return [];
						}
						
						$options = [];
						foreach ($menuStructure[$parent] as $menuItem) {
							$options[] = [
								'depth' => $depth,
								'label' => $menuItem->menuItem,
								'value' => $menuItem->menuItem
							];
							
							if (isset($menuStructure[$menuItem->menuItem])) {
								$options = array_merge($options, $buildOptions($menuItem->menuItem, $depth + 1));
							}
						}
						
						return $options;
					};
					
					return array_merge($options, $buildOptions());
				}, true)
				->value(''),
			
			ClassNameFormField::create('menuItemController')
				->objectProperty('controller')
				->label('wcf.acp.pip.abstractForm.menuItemController')
				->implementedInterface(IPage::class),
			
			TextFormField::create('menuItemLink')
				->objectProperty('link')
				->label('wcf.acp.pip.abstractMenu.menuItemLink')
				->description('wcf.acp.pip.abstractMenu.menuItemLink.description')
				->objectProperty('link')
				->addValidator(new FormFieldValidator('linkSpecified', function(TextFormField $formField) {
					/** @var ClassNameFormField $menuItemController */
					$menuItemController = $formField->getDocument()->getNodeById('menuItemController');
					
					// ensure that either a menu item controller is specified or a link
					if ($formField->getSaveValue() === '' && $menuItemController->getSaveValue() === '') {
						$formField->addValidationError(
							new FormFieldValidationError(
								'noLinkSpecified',
								'wcf.acp.pip.abstractMenu.menuItemLink.error.noLinkSpecified'
							)
						);
					}
				}))
				->addValidator(new FormFieldValidator('format', function(TextFormField $formField) {
					if ($formField->getSaveValue() !== '') {
						/** @var ClassNameFormField $menuItemController */
						$menuItemController = $formField->getDocument()->getNodeById('menuItemController');
						
						if (!$menuItemController->getSaveValue() && !Url::is($formField->getSaveValue())) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'noLink',
									'wcf.acp.pip.abstractMenu.menuItemLink.error.noLink'
								)
							);
						}
						else if ($menuItemController->getSaveValue() && Url::is($formField->getSaveValue())) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'isLink',
									'wcf.acp.pip.abstractMenu.menuItemLink.error.isLink'
								)
							);
						}
					}
				})),
			
			OptionFormField::create()
				->description('wcf.acp.pip.abstractMenu.options.description')
				->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
				->packageIDs(array_merge(
					[$this->installation->getPackage()->packageID],
					array_keys($this->installation->getPackage()->getAllRequiredPackages())
				)),
			
			UserGroupOptionFormField::create()
				->description('wcf.acp.pip.abstractMenu.options.description')
				->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
				->packageIDs(array_merge(
					[$this->installation->getPackage()->packageID],
					array_keys($this->installation->getPackage()->getAllRequiredPackages())
				)),
			
			IntegerFormField::create('showOrder')
				->objectProperty('showorder')
				->label('wcf.acp.pip.abstractMenu.showOrder')
				->description('wcf.acp.pip.abstractMenu.showOrder.description')
				->objectProperty('showorder')
				->minimum(1)
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getElementData(\DOMElement $element, $saveData = false) {
		$data = [
			'menuItem' => $element->getAttribute('name'),
			'packageID' => $this->installation->getPackage()->packageID
		];
		
		$parentMenuItem = $element->getElementsByTagName('parent')->item(0);
		if ($parentMenuItem !== null) {
			$data['parentMenuItem'] = $parentMenuItem->nodeValue;
		}
		
		$controller = $element->getElementsByTagName('controller')->item(0);
		if ($controller !== null) {
			$data['menuItemController'] = $controller->nodeValue;
		}
		
		$link = $element->getElementsByTagName('link')->item(0);
		if ($link !== null) {
			$data['menuItemLink'] = $link->nodeValue;
		}
		
		$options = $element->getElementsByTagName('options')->item(0);
		if ($options !== null) {
			$data['options'] = $options->nodeValue;
		}
		
		$permissions = $element->getElementsByTagName('permissions')->item(0);
		if ($permissions !== null) {
			$data['permissions'] = $permissions->nodeValue;
		}
		
		$showOrder = $element->getElementsByTagName('showOrder')->item(0);
		if ($showOrder !== null) {
			$data['showOrder'] = $showOrder->nodeValue;
		}
		
		return $data;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getElementIdentifier(\DOMElement $element) {
		return $element->getAttribute('name');
	}
	
	/**
	 * Returns data on the structure of the menu.
	 * 
	 * @return	array
	 */
	protected function getMenuStructureData() {
		// replace `Editor` with `List`
		$listClassName = substr($this->className, 0, -6) . 'List';
		
		/** @var DatabaseObjectList $menuItemList */
		$menuItemList = new $listClassName();
		$menuItemList->getConditionBuilder()->add('packageID IN (?)', [array_merge(
			[$this->installation->getPackage()->packageID],
			array_keys($this->installation->getPackage()->getAllRequiredPackages())
		)]);
		$menuItemList->sqlOrderBy = 'parentMenuItem ASC, showOrder ASC';
		$menuItemList->readObjects();
		
		// for better IDE auto-completion, we use `ACPMenuItem`, but the
		// menu items can also belong to other menus
		/** @var ACPMenuItem[] $menuItems */
		$menuItems = [];
		/** @var ACPMenuItem[][] $menuStructure */
		$menuStructure = [];
		foreach ($menuItemList as $menuItem) {
			if (!isset($menuStructure[$menuItem->parentMenuItem])) {
				$menuStructure[$menuItem->parentMenuItem] = [];
			}
			
			$menuStructure[$menuItem->parentMenuItem][$menuItem->menuItem] = $menuItem;
			$menuItems[$menuItem->menuItem] = $menuItem;
		}
		
		$menuItemLevels = [];
		foreach ($menuStructure as $parentMenuItemName => $childMenuItems) {
			$menuItemsLevel = 1;
			
			while (($parentMenuItem = $menuItems[$parentMenuItemName] ?? null)) {
				$menuItemsLevel++;
				$parentMenuItemName = $parentMenuItem->parentMenuItem;
			}
			
			foreach ($childMenuItems as $menuItem) {
				$menuItemLevels[$menuItem->menuItem] = $menuItemsLevel;
			}
		}
		
		return [
			'levels' => $menuItemLevels,
			'structure' => $menuStructure
		];
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function setEntryListKeys(IDevtoolsPipEntryList $entryList) {
		$entryList->setKeys([
			'menuItem' => 'wcf.acp.pip.abstractMenu.menuItem',
			'parentMenuItem' => 'wcf.acp.pip.abstractMenu.parentMenuItem'
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function createXmlElement(\DOMDocument $document, IFormDocument $form) {
		$formData = $form->getData()['data'];
		
		$menuItem = $document->createElement($this->tagName);
		$menuItem->setAttribute('name', $formData['name']);
		
		foreach (['parent', 'controller', 'link', 'options', 'permissions', 'showorder'] as $field) {
			if (isset($formData[$field]) && $formData[$field] !== '') {
				$menuItem->appendChild($document->createElement($field, (string) $formData[$field]));
			}
		}
		
		return $menuItem;
	}
}
