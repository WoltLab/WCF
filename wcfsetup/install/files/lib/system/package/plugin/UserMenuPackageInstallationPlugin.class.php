<?php
declare(strict_types=1);
namespace wcf\system\package\plugin;
use wcf\data\user\menu\item\UserMenuItemEditor;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IconFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\form\builder\IFormDocument;
use wcf\system\menu\user\IUserMenuItemProvider;

/**
 * Installs, updates and deletes user menu items.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class UserMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $className = UserMenuItemEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tableName = 'user_menu_item';
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'usermenuitem';
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$result = parent::prepareImport($data);
		
		// class name
		if (!empty($data['elements']['classname'])) {
			$result['className'] = $data['elements']['classname'];
		}
		
		// FontAwesome icon name
		if (!empty($data['elements']['iconclassname']) && preg_match('~^fa\-[a-z\-]+$~', $data['elements']['iconclassname'])) {
			$result['iconClassName'] = $data['elements']['iconclassname'];
		}
		
		return $result;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function addFormFields(IFormDocument $form) {
		parent::addFormFields($form);
		
		/** @var IFormContainer $dataContainer */
		$dataContainer = $form->getNodeById('data');
		
		// add menu item className form field
		
		$classNameFormField = ClassNameFormField::create()
			->implementedInterface(IUserMenuItemProvider::class);
		$dataContainer->insertBefore($classNameFormField, 'menuItemController');
		
		// add menu item icon form field
		
		/** @var SingleSelectionFormField $parentMenuItemFormField */
		$parentMenuItemFormField = $form->getNodeById('parentMenuItem');
		
		$dataContainer->appendChild(IconFormField::create('iconClassName')
			->objectProperty('iconclassname')
			->label('wcf.acp.pip.userMenu.iconClassName')
			->description('wcf.acp.pip.userMenu.iconClassName.description')
			->required()
			->addDependency(
				// only first level menu items support icons
				ValueFormFieldDependency::create('parentMenuItem')
					->field($parentMenuItemFormField)
					->values([''])
			));
		
		// add additional data to default fields
		
		/** @var TextFormField $menuItemFormField */
		$menuItemFormField = $form->getNodeById('menuItem');
		$menuItemFormField
			->description('wcf.acp.pip.userMenu.menuItem.description')
			->addValidator(FormFieldValidatorUtil::getRegularExpressionValidator(
				'[a-z]+\.user.menu(\.[A-z0-9])+',
				'wcf.acp.pip.userMenu.menuItem'
			));
		
		// add dependencies to default fields
		
		$menuItemLevels = ['' => 0] + $this->getMenuStructureData()['levels'];
		
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
	protected function getElementData(\DOMElement $element): array {
		$data = parent::getElementData($element);
		
		$className = $element->getElementsByTagName('classname')->item(0);
		if ($className !== null) {
			$data['className'] = $className->nodeValue;
		}
		
		$icon = $element->getElementsByTagName('iconclassname')->item(0);
		if ($icon !== null) {
			$data['iconClassName'] = $icon->nodeValue;
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
		
		if (!empty($formData['classname'])) {
			$menuItem->appendChild($document->createElement('classname', $formData['classname']));
		}
		if (!empty($formData['iconclassname'])) {
			$menuItem->appendChild($document->createElement('iconclassname', $formData['iconclassname']));
		}
		
		return $menuItem;
	}
}
