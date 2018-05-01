<?php
declare(strict_types=1);
namespace wcf\system\package\plugin;
use wcf\data\option\Option;
use wcf\data\user\group\option\UserGroupOptionList;
use wcf\data\user\profile\menu\item\UserProfileMenuItemEditor;
use wcf\data\user\profile\menu\item\UserProfileMenuItemList;
use wcf\system\devtools\pip\DevtoolsPipEntryList;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\menu\user\profile\content\IUserProfileMenuContent;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Installs, updates and deletes user profile menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class UserProfileMenuPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	use TXmlGuiPackageInstallationPlugin;
	
	/**
	 * @inheritDoc
	 */
	public $className = UserProfileMenuItemEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tableName = 'user_profile_menu_item';
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'userprofilemenuitem';
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
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
		$showOrder = $this->getShowOrder($showOrder);
		
		// merge values and default values
		return [
			'menuItem' => $data['attributes']['name'],
			'options' => isset($data['elements']['options']) ? StringUtil::normalizeCsv($data['elements']['options']) : '',
			'permissions' => isset($data['elements']['permissions']) ? StringUtil::normalizeCsv($data['elements']['permissions']) : '',
			'showOrder' => $showOrder,
			'className' => $data['elements']['classname']
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
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
		$form->getNodeById('data')->appendChildren([
			TextFormField::create('name')
				->label('wcf.acp.pip.userProfileMenu.name')
				->description('wcf.acp.pip.userProfileMenu.name.description')
				->required()
				->addValidator(new FormFieldValidator('format', function(TextFormField $formField) {
					if (!preg_match('~^[a-z][A-z]+$~', $formField->getValue())) {
						$formField->addValidationError(
							new FormFieldValidationError(
								'format',
								'wcf.acp.pip.userProfileMenu.name.error.format'
							)
						);
					}
				}))
				->addValidator(new FormFieldValidator('uniqueness', function(TextFormField $formField) {
					if ($formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE || $this->editedEntry->getAttribute('name') !== $formField->getValue()) {
						$menuItemList = new UserProfileMenuItemList();
						$menuItemList->getConditionBuilder()->add('user_profile_menu_item.menuItem = ?', [$formField->getValue()]);
						
						if ($menuItemList->countObjects() > 0) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'notUnique',
									'wcf.acp.pip.userProfileMenu.name.error.notUnique'
								)
							);
						}
					}
				})),
			
			ClassNameFormField::create('className')
				->attribute('data-tag', 'classname')
				->label('wcf.acp.pip.userProfileMenu.className')
				->description('wcf.acp.pip.userProfileMenu.className.description')
				->required()
				->implementedInterface(IUserProfileMenuContent::class),
			
			IntegerFormField::create('showOrder')
				->attribute('data-tag', 'showorder')
				->label('wcf.acp.pip.userProfileMenu.showOrder')
				->description('wcf.acp.pip.userProfileMenu.showOrder.description')
				->nullable()
				->minimum(1),
			
			ItemListFormField::create('options')
				->label('wcf.acp.pip.general.options')
				->description('wcf.acp.pip.userProfileMenu.options.description')
				->addValidator(new FormFieldValidator('optionsExist', function(ItemListFormField $formField) {
					$options = $formField->getValue();
					if (is_array($options)) {
						$definedOptions = Option::getOptions();
						
						$options = array_filter($options, function(string $option) use ($definedOptions) {
							return !isset($definedOptions[strtoupper($option)]);
						});
						
						if (!empty($options)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'nonExistent',
									'wcf.acp.pip.general.options.error.nonExistent',
									['options' => $options]
								)
							);
						}
					}
				})),
			
			ItemListFormField::create('permissions')
				->label('wcf.acp.pip.general.permissions')
				->description('wcf.acp.pip.userProfileMenu.permissions.description')
				->addValidator(new FormFieldValidator('permissionsExist', function(ItemListFormField $formField) {
					$permissions = $formField->getValue();
					if (is_array($permissions)) {
						$userGroupOptionList = new UserGroupOptionList();
						$userGroupOptionList->getConditionBuilder()->add('optionName IN (?)', [$permissions]);
						$userGroupOptionList->readObjects();
						
						if (count($userGroupOptionList) !== count($permissions)) {
							foreach ($userGroupOptionList as $userGroupOption) {
								unset($permissions[array_search($userGroupOption->optionName, $permissions)]);
							}
							
							$formField->addValidationError(
								new FormFieldValidationError(
									'nonExistent',
									'wcf.acp.pip.general.permissions.error.nonExistent',
									['permissions' => $permissions]
								)
							);
						}
					}
				}))
		]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getElementData(\DOMElement $element): array {
		$data = [
			'className' => $element->getElementsByTagName('classname')->item(0)->nodeValue,
			'menuItem' => $element->getAttribute('name'),
			'packageID' => $this->installation->getPackage()->packageID
		];
		
		$options = $element->getElementsByTagName('options')->item(0);
		if ($options) {
			$data['options'] = StringUtil::normalizeCsv($options->nodeValue);
		}
		
		$permissions = $element->getElementsByTagName('permissions')->item(0);
		if ($permissions) {
			$data['permissions'] = StringUtil::normalizeCsv($permissions->nodeValue);
		}
		
		$showOrder = $element->getElementsByTagName('showorder')->item(0);
		if ($showOrder) {
			$data['showOrder'] = intval($showOrder->nodeValue);
		}
		else {
			$data['showOrder'] = null;
		}
		
		return $data;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getElementIdentifier(\DOMElement $element): string {
		return $element->getAttribute('name');
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getEntryList(): IDevtoolsPipEntryList {
		$xml = $this->getProjectXml();
		$xpath = $xml->xpath();
		
		$entryList = new DevtoolsPipEntryList();
		$entryList->setKeys([
			'name' => 'wcf.acp.pip.userProfileMenu.name',
			'className' => 'wcf.acp.pip.userProfileMenu.className'
		]);
		
		/** @var \DOMElement $element */
		foreach ($this->getImportElements($xpath) as $element) {
			$entryList->addEntry($this->getElementIdentifier($element), [
				'className' => $element->getElementsByTagName('classname')->item(0)->nodeValue,
				'name' => $element->getAttribute('name'),
			]);
		}
		
		return $entryList;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function sortDocument(\DOMDocument $document) {
		$this->sortImportDelete($document);
		
		$compareFunction = function(\DOMElement $element1, \DOMElement $element2) {
			$showOrder1 = PHP_INT_MAX;
			if ($element1->getElementsByTagName('showorder')->length === 1) {
				$showOrder1 = $element1->getElementsByTagName('showorder')->item(0)->nodeValue;
			}
			
			$showOrder2 = PHP_INT_MAX;
			if ($element2->getElementsByTagName('showorder')->length === 1) {
				$showOrder2 = $element2->getElementsByTagName('showorder')->item(0)->nodeValue;
			}
			
			if ($showOrder1 !== $showOrder2) {
				return $showOrder1 > $showOrder2;
			}
			
			return strcmp(
				$element1->getAttribute('name'),
				$element2->getAttribute('name')
			);
		};
		
		$this->sortChildNodes($document->getElementsByTagName('import'), $compareFunction);
		$this->sortChildNodes($document->getElementsByTagName('delete'), $compareFunction);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function writeEntry(\DOMDocument $document, IFormDocument $form): \DOMElement {
		$userProfileMenuItem = $document->createElement('userprofilemenuitem');
		$userProfileMenuItem->setAttribute('name', $form->getNodeById('name')->getSaveValue());
		$userProfileMenuItem->appendChild($document->createElement('classname', $form->getNodeById('className')->getSaveValue()));
		
		/** @var ItemListFormField $options */
		$options = $form->getNodeById('options');
		if ($options->getSaveValue()) {
			$userProfileMenuItem->appendChild($document->createElement('options', $options->getSaveValue()));
		}
		
		/** @var ItemListFormField $permissions */
		$permissions = $form->getNodeById('permissions');
		if ($permissions->getSaveValue()) {
			$userProfileMenuItem->appendChild($document->createElement('permissions', $permissions->getSaveValue()));
		}
		
		/** @var IntegerFormField $showOrder */
		$showOrder = $form->getNodeById('showOrder');
		if ($showOrder->getSaveValue()) {
			$userProfileMenuItem->appendChild($document->createElement('showorder', (string) $showOrder->getSaveValue()));
		}
		
		$import = $document->getElementsByTagName('import')->item(0);
		$import->appendChild($userProfileMenuItem);
		
		return $userProfileMenuItem;
	}
}
