<?php
declare(strict_types=1);
namespace wcf\system\package\plugin;
use wcf\data\acp\menu\item\ACPMenuItem;
use wcf\data\acp\menu\item\ACPMenuItemEditor;
use wcf\data\acp\menu\item\ACPMenuItemList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\form\builder\field\validation\RegularExpressionFormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;

/**
 * Installs, updates and deletes ACP menu items.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class ACPMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = ACPMenuItemEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$returnValue = parent::prepareImport($data);
		
		$returnValue['icon'] = isset($data['elements']['icon']) ? $data['elements']['icon'] : '';
		
		return $returnValue;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.0
	 */
	public static function getDefaultFilename() {
		return 'acpMenu.xml';
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function addFormFields(IFormDocument $form) {
		parent::addFormFields($form);
		
		/** @var IFormContainer $dataContainer */
		$dataContainer = $form->getNodeById('data');
		
		// add parent menu item options
		
		$acpMenuStructureData = $this->getACPMenuStructureData();
		$acpMenuStructure = $acpMenuStructureData['structure'];
		$menuItemLevels = ['' => 0] + $acpMenuStructureData['levels'];
		
		// only consider menu items until the third level (thus only parent
		// menu items until the second level) as potential parent menu items
		$acpMenuStructure = array_filter($acpMenuStructure, function(string $parentMenuItem) use ($menuItemLevels): bool {
			return $menuItemLevels[$parentMenuItem] <= 2;
		}, ARRAY_FILTER_USE_KEY);
		
		// icons are only available for menu items on the first or fourth level
		// thus the parent menu item must be on zeroth level (no parent menu item)
		// or on the third level
		$iconParentMenuItems = array_keys(array_filter($menuItemLevels, function(int $value): bool {
			return $value === 0 || $value == 3;
		}));
		
		$buildOptions = function(string $parent = '', int $level = 0) use ($acpMenuStructure, &$buildOptions): array {
			$options = [];
			foreach ($acpMenuStructure[$parent] as $menuItem) {
				$options[$menuItem->menuItem] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . WCF::getLanguage()->get($menuItem->menuItem);
				
				if (isset($acpMenuStructure[$menuItem->menuItem])) {
					$options += $buildOptions($menuItem->menuItem, $level + 1);
				}
			}
			
			return $options;
		};
		
		/** @var SingleSelectionFormField $parentMenuItemFormField */
		$parentMenuItemFormField = $dataContainer->getNodeById('parentMenuItem');
		$parentMenuItemFormField
			->options(['' => 'wcf.global.noSelection'] + $buildOptions())
			->value('');
		
		// add menu icon form field
		
		// TODO: if an `IconFormField` class should be added, use that class instead 
		$dataContainer->appendChild(SingleSelectionFormField::create('icon')
			->label('wcf.acp.pip.acpMenu.icon')
			->description('wcf.acp.pip.acpMenu.icon.description')
			->filterable()
			->options(function(): array {
				$icons = array_map(function(string $icon): string {
					return 'fa-' . $icon;
				}, StyleHandler::getInstance()->getIcons());
				
				return ['' => 'wcf.global.noSelection'] + array_combine($icons, $icons);
			})
			->addDependency(
				ValueFormFieldDependency::create('parentMenuItem')
					->field($parentMenuItemFormField)
					->values($iconParentMenuItems)
			));
		
		// add additional data to default fields
		
		/** @var TextFormField $menuItemFormField */
		$menuItemFormField = $form->getNodeById('menuItem');
		$menuItemFormField
			->description('wcf.acp.pip.acpMenu.menuItem.description')
			->addValidator(FormFieldValidatorUtil::getRegularExpressionValidator(
				'[a-z]+\.acp\.menu\.link(\.[A-z0-9])+',
				'wcf.acp.pip.acpMenu.menuItem'
			))
			->addValidator(new FormFieldValidator('uniqueness', function(TextFormField $formField) {
				if (
					$formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE ||
					$this->editedEntry->getAttribute('name') !== $formField->getValue()
				) {
					$menuItemName = new ACPMenuItemList();
					$menuItemName->getConditionBuilder()->add('menuItem = ?', [$formField->getValue()]);
					
					if ($menuItemName->countObjects() > 0) {
						$formField->addValidationError(
							new FormFieldValidationError(
								'notUnique',
								'wcf.acp.pip.abstractMenu.menuItem.error.notUnique'
							)
						);
					}
				}
			}));
		
		/** @var TextFormField $menuItemControllerFormField */
		$menuItemControllerFormField = $form->getNodeById('menuItemController');
		$menuItemControllerFormField->addValidator(new FormFieldValidator('acpController', function(TextFormField $formField) {
			// the controller must be an ACP controller
			if ($formField->getSaveValue() !== '' && !preg_match("~^[a-z]+\\\\acp\\\\~", $formField->getSaveValue())) {
				$formField->addValidationError(
					new FormFieldValidationError(
						'noAcpController',
						'wcf.acp.pip.acpMenu.menuItemController.error.noAcpController'
					)
				);
			}
		}));
		
		// add dependencies to default fields
		
		// menu items on the first and second level do not support links,
		// thus the parent menu item must be at least on the second level
		// for the menu item to support links
		$menuItemsSupportingLinks = array_keys(array_filter($menuItemLevels, function(int $menuItemLevel): bool {
			return $menuItemLevel >= 2;
		}));
		
		foreach (['menuItemController', 'menuItemLink'] as $nodeId) {
			/** @var TextFormField $formField */
			$formField = $form->getNodeById($nodeId);
			$formField->addDependency(
				ValueFormFieldDependency::create('parentMenuItem')
					->field($parentMenuItemFormField)
					->values($menuItemsSupportingLinks)
			);
		}
	}
	
	/**
	 * Returns data on the structure of the acp menu.
	 * 
	 * @return	array
	 */
	protected function getACPMenuStructureData(): array {
		$acpMenuItemList = new ACPMenuItemList();
		$acpMenuItemList->getConditionBuilder()->add('packageID IN (?)', [array_merge(
			[$this->installation->getPackage()->packageID],
			array_keys($this->installation->getPackage()->getAllRequiredPackages())
		)]);
		$acpMenuItemList->sqlOrderBy = 'parentMenuItem ASC, showOrder ASC';
		$acpMenuItemList->readObjects();
		
		/** @var ACPMenuItem[] $acpMenuItems */
		$acpMenuItems = [];
		/** @var ACPMenuItem[][] $acpMenuStructure */
		$acpMenuStructure = [];
		foreach ($acpMenuItemList as $menuItem) {
			if (!isset($acpMenuStructure[$menuItem->parentMenuItem])) {
				$acpMenuStructure[$menuItem->parentMenuItem] = [];
			}
			
			$acpMenuStructure[$menuItem->parentMenuItem][$menuItem->menuItem] = $menuItem;
			$acpMenuItems[$menuItem->menuItem] = $menuItem;
		}
		
		$menuItemLevels = [];
		foreach ($acpMenuStructure as $parentMenuItemName => $menuItems) {
			$menuItemsLevel = 1;
			
			while (($parentMenuItem = $acpMenuItems[$parentMenuItemName] ?? null)) {
				$menuItemsLevel++;
				$parentMenuItemName = $parentMenuItem->parentMenuItem;
			}
			
			foreach ($menuItems as $menuItem) {
				$menuItemLevels[$menuItem->menuItem] = $menuItemsLevel;
			}
		}
		
		return [
			'levels' => $menuItemLevels,
			'structure' => $acpMenuStructure
		];
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function sortDocument(\DOMDocument $document) {
		$acpMenuStructureData = $this->getACPMenuStructureData();
		/** @var ACPMenuItem[][] $menuItemStructure */
		$menuItemStructure = $acpMenuStructureData['structure'];
		
		$this->sortImportDelete($document);
		
		// build array containing the ACP menu items saved in the database
		// in the order as they would be displayed in the ACP
		$buildPositions = function(string $parent = '') use ($menuItemStructure, &$buildPositions): array {
			$positions = [];
			foreach ($menuItemStructure[$parent] as $menuItem) {
				// only consider menu items of the current package for positions
				if ($menuItem->packageID === $this->installation->getPackageID()) {
					$positions[] = $menuItem->menuItem;
				}
				
				if (isset($menuItemStructure[$menuItem->menuItem])) {
					$positions = array_merge($positions, $buildPositions($menuItem->menuItem));
				}
			}
			
			return $positions;
		};
		
		// flip positions array so that the keys are the menu item names
		// and the values become the positions so that the array values
		// can be used in the sort function
		$positions = array_flip($buildPositions());
		
		$compareFunction = function(\DOMElement $element1, \DOMElement $element2) use ($positions) {
			return $positions[$element1->getAttribute('name')] <=> $positions[$element2->getAttribute('name')];
		};
		
		$this->sortChildNodes($document->getElementsByTagName('import'), $compareFunction);
		$this->sortChildNodes($document->getElementsByTagName('delete'), $compareFunction);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function writeEntry(\DOMDocument $document, IFormDocument $form): \DOMElement {
		$formData = $form->getData()['data'];
		
		$menuItem = parent::writeEntry($document, $form);
		
		if (isset($formData['icon'])) {
			$menuItem->appendChild($document->createElement('icon', $formData['icon']));
		}
		
		return $menuItem;
	}
}
