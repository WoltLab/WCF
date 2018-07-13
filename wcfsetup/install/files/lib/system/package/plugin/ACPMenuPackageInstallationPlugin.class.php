<?php
declare(strict_types=1);
namespace wcf\system\package\plugin;
use wcf\data\acp\menu\item\ACPMenuItemEditor;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IconFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\form\builder\IFormDocument;

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
		
		// add menu item icon form field
		
		/** @var SingleSelectionFormField $parentMenuItemFormField */
		$parentMenuItemFormField = $form->getNodeById('parentMenuItem');
		
		$menuItemLevels = ['' => 0] + $this->getMenuStructureData()['levels'];
		
		// icons are only available for menu items on the first or fourth level
		// thus the parent menu item must be on zeroth level (no parent menu item)
		// or on the third level
		$iconParentMenuItems = array_keys(array_filter($menuItemLevels, function(int $value): bool {
			return $value === 0 || $value == 3;
		}));
		
		$dataContainer->appendChild(IconFormField::create('icon')
			->label('wcf.acp.pip.acpMenu.icon')
			->description('wcf.acp.pip.acpMenu.icon.description')
			->required()
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
			));
		
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
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getElementData(\DOMElement $element, bool $saveData = false): array {
		$data = parent::getElementData($element);
		
		$icon = $element->getElementsByTagName('icon')->item(0);
		if ($icon !== null) {
			$data['icon'] = $icon->nodeValue;
		}
		
		return $data;
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
