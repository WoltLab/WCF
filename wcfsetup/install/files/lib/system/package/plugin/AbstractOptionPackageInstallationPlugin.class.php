<?php
namespace wcf\system\package\plugin;
use wcf\data\DatabaseObject;
use wcf\data\option\category\OptionCategory;
use wcf\data\package\Package;
use wcf\system\application\ApplicationHandler;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\OptionFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\option\II18nOptionType;
use wcf\system\option\IOptionHandler;
use wcf\system\option\IOptionType;
use wcf\system\option\ISelectOptionOptionType;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a package installation plugin for options.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
abstract class AbstractOptionPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IIdempotentPackageInstallationPlugin {
	// we do no implement `IGuiPackageInstallationPlugin` but instead just
	// provide the default implementation to ensure backwards compatibility
	// with third-party packages containing classes that extend this abstract
	// class
	use TXmlGuiPackageInstallationPlugin;
	
	/**
	 * list of option types with i18n support
	 * @var string[]
	 */
	public $i18nOptionTypes = [];
	
	/**
	 * list of option types with a pre-defined list of options via `selectOptions`
	 * @var	string[]
	 */
	public $selectOptionOptionTypes = [];
	
	/**
	 * @inheritDoc
	 */
	public function install() {
		AbstractPackageInstallationPlugin::install();
		
		$xml = $this->getXML($this->instruction['value']);
		$xpath = $xml->xpath();
		
		if ($this->installation->getAction() == 'update') {
			// handle delete first
			$this->deleteItems($xpath);
		}
		
		// import or update categories
		$this->importCategories($xpath);
		
		// import or update options
		$this->importOptions($xpath);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function deleteItems(\DOMXPath $xpath) {
		// delete options
		$elements = $xpath->query('/ns:data/ns:delete/ns:option');
		$options = [];
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$options[] = $element->getAttribute('name');
		}
		
		if (!empty($options)) {
			$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."
				WHERE		optionName = ?
				AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($options as $option) {
				$statement->execute([
					$option,
					$this->installation->getPackageID()
				]);
			}
		}
		
		// delete categories
		$elements = $xpath->query('/ns:data/ns:delete/ns:optioncategory');
		$categories = [];
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$categories[] = $element->getAttribute('name');
		}
		
		if (!empty($categories)) {
			// delete options for given categories
			$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."
				WHERE		categoryName = ?
						AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($categories as $category) {
				$statement->execute([
					$category,
					$this->installation->getPackageID()
				]);
			}
			
			// delete categories
			$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."_category
				WHERE		categoryName = ?
				AND		packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($categories as $category) {
				$statement->execute([
					$category,
					$this->installation->getPackageID()
				]);
			}
		}
	}
	
	/**
	 * Imports option categories.
	 * 
	 * @param	\DOMXPath	$xpath
	 * @throws	SystemException
	 */
	protected function importCategories(\DOMXPath $xpath) {
		$elements = $xpath->query('/ns:data/ns:import/ns:categories/ns:category');
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$data = [];
			
			// get child elements
			$children = $xpath->query('child::*', $element);
			foreach ($children as $child) {
				$data[$child->tagName] = $child->nodeValue;
			}
			
			// build data block with defaults
			$data = [
				'categoryName' => $element->getAttribute('name'),
				'options' => isset($data['options']) ? StringUtil::normalizeCsv($data['options']) : '',
				'parentCategoryName' => isset($data['parent']) ? $data['parent'] : '',
				'permissions' => isset($data['permissions']) ? StringUtil::normalizeCsv($data['permissions']) : '',
				'showOrder' => isset($data['showorder']) ? intval($data['showorder']) : null
			];
			
			// adjust show order
			if ($data['showOrder'] !== null || $this->installation->getAction() != 'update' || $this->getExistingCategory($element->getAttribute('name')) === false) {
				$data['showOrder'] = $this->getShowOrder($data['showOrder'], $data['parentCategoryName'], 'parentCategoryName', '_category');
			}
			
			// validate parent
			if (!empty($data['parentCategoryName'])) {
				$sql = "SELECT	COUNT(categoryID)
					FROM	".$this->application.WCF_N."_".$this->tableName."_category
					WHERE	categoryName = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([$data['parentCategoryName']]);
				
				if (!$statement->fetchSingleColumn()) {
					throw new SystemException("Unable to find parent 'option category' with name '".$data['parentCategoryName']."' for category with name '".$data['categoryName']."'.");
				}
			}
			
			// save category
			$this->saveCategory($data);
		}
	}
	
	/**
	 * Imports options.
	 * 
	 * @param	\DOMXPath	$xpath
	 */
	protected function importOptions(\DOMXPath $xpath) {
		$elements = $xpath->query('/ns:data/ns:import/ns:options/ns:option');
		
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			$data = [];
			$children = $xpath->query('child::*', $element);
			foreach ($children as $child) {
				$data[$child->tagName] = $child->nodeValue;
			}
			
			$data['name'] = $element->getAttribute('name');
			
			$this->validateOption($data);
			$this->saveOption($data, $data['categoryname']);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasUninstall() {
		$hasUninstallOptions = parent::hasUninstall();
		$sql = "SELECT	COUNT(categoryID)
			FROM	".$this->application.WCF_N."_".$this->tableName."_category
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->installation->getPackageID()]);
		
		return ($hasUninstallOptions || $statement->fetchSingleColumn() > 0);
	}
	
	/**
	 * @inheritDoc
	 */
	public function uninstall() {
		// delete options
		parent::uninstall();
		
		// delete categories
		$sql = "DELETE FROM	".$this->application.WCF_N."_".$this->tableName."_category
			WHERE		packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->installation->getPackageID()]);
	}
	
	/**
	 * Returns the category with given name. 
	 * 
	 * @param       string          $category
	 * @return      array|false
	 */
	protected function getExistingCategory($category) {
		$sql = "SELECT	categoryID, packageID
			FROM	".$this->application.WCF_N."_".$this->tableName."_category
			WHERE	categoryName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$category
		]);
		return $statement->fetchArray();
	}
	
	/**
	 * Installs option categories.
	 * 
	 * @param	array		$category
	 * @throws	SystemException
	 */
	protected function saveCategory($category) {
		// search existing category
		$row = $this->getExistingCategory($category['categoryName']);
		if (empty($row['categoryID'])) {
			// insert new category
			$sql = "INSERT INTO	".$this->application.WCF_N."_".$this->tableName."_category
						(packageID, categoryName, parentCategoryName, permissions,
						options".($category['showOrder'] !== null ? ",showOrder" : "").")
				VALUES		(?, ?, ?, ?, ?".($category['showOrder'] !== null ? ", ?" : "").")";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			$data = [
				$this->installation->getPackageID(),
				$category['categoryName'],
				$category['parentCategoryName'],
				$category['permissions'],
				$category['options']
			];
			if ($category['showOrder'] !== null) $data[] = $category['showOrder'];
			
			$statement->execute($data);
		}
		else {
			if ($row['packageID'] != $this->installation->getPackageID()) {
				throw new SystemException("Cannot override existing category '".$category['categoryName']."'");
			}
			
			// update existing category
			$sql = "UPDATE	".$this->application.WCF_N."_".$this->tableName."_category
				SET	parentCategoryName = ?,
					permissions = ?,
					options = ?
					".($category['showOrder'] !== null ? ", showOrder = ?" : "")."
				WHERE	categoryID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			$data = [
				$category['parentCategoryName'],
				$category['permissions'],
				$category['options']
			];
			if ($category['showOrder'] !== null) $data[] = $category['showOrder'];
			$data[] = $row['categoryID'];
			
			$statement->execute($data);
		}
	}
	
	/**
	 * Installs options.
	 * 
	 * @param	array		$option
	 * @param	string		$categoryName
	 * @param	integer		$existingOptionID
	 */
	abstract protected function saveOption($option, $categoryName, $existingOptionID = 0);
	
	/**
	 * @inheritDoc
	 */
	protected function validateOption(array $data) {
		if (!preg_match("/^[\w\-\.]+$/", $data['name'])) {
			$matches = [];
			preg_match_all("/(\W)/", $data['name'], $matches);
			throw new SystemException("The option '".$data['name']."' has at least one non-alphanumeric character (underscore is permitted): (".implode("), ( ", $matches[1]).").");
		}
		
		// check if option already exists
		$sql = "SELECT	*
			FROM	".$this->application.WCF_N."_".$this->tableName."
			WHERE	optionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$data['name']
		]);
		$row = $statement->fetchArray();
		if ($row && $row['packageID'] != $this->installation->getPackageID()) {
			$package = new Package($row['packageID']);
			throw new SystemException($this->tableName . " '" . $data['name'] . "' is already provided by '" . $package . "' ('" . $package->package . "').");
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) { }
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) { }
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) { }
	
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
		
		switch ($this->entryType) {
			case 'categories':
				$dataContainer->appendChildren([
					TextFormField::create('categoryName')
						->label('wcf.acp.pip.abstractOption.categories.categoryName')
						->description('wcf.acp.pip.' . $this->tagName . '.categories.categoryName.description')
						->required(),
					
					SingleSelectionFormField::create('parentCategoryName')
						->label('wcf.acp.pip.abstractOption.categories.parentCategoryName')
						->options(function() {
							$categories = $this->getSortedCategories();
							
							$getDepth = function(/** @var OptionCategory $category */$category) use ($categories) {
								$depth = 0;
								
								while (isset($categories[$category->parentCategoryName])) {
									$depth++;
									
									$category = $categories[$category->parentCategoryName];
								}
								
								return $depth;
							};
							
							$options = [];
							/** @var OptionCategory $category */
							foreach ($categories as $category) {
								$depth = $getDepth($category);
								
								// the maximum nesting level is three
								if ($depth <= 1) {
									$options[] = [
										'depth' => $depth,
										'label' => $category->categoryName,
										'value' => $category->categoryName
									];
								}
							}
							
							return $options;
						}, true),
					
					IntegerFormField::create('showOrder')
						->label('wcf.acp.pip.abstractOption.categories.showOrder')
						->description('wcf.acp.pip.abstractOption.categories.showOrder.description')
						->nullable(),
					
					OptionFormField::create()
						->description('wcf.acp.pip.abstractOption.categories.options.description')
						->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
						->packageIDs(array_merge(
							[$this->installation->getPackage()->packageID],
							array_keys($this->installation->getPackage()->getAllRequiredPackages())
						)),
					
					UserGroupOptionFormField::create()
						->description('wcf.acp.pip.abstractOption.categories.permissions.description')
						->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
						->packageIDs(array_merge(
							[$this->installation->getPackage()->packageID],
							array_keys($this->installation->getPackage()->getAllRequiredPackages())
						))
				]);
				
				break;
			
			case 'options':
				$dataContainer->appendChildren([
					TextFormField::create('optionName')
						->objectProperty('name')
						->label('wcf.acp.pip.abstractOption.options.optionName')
						->description('wcf.acp.pip.abstractOption.categories.options.description')
						->required(),
					
					SingleSelectionFormField::create('categoryName')
						->objectProperty('categoryname')
						->label('wcf.acp.pip.abstractOption.options.categoryName')
						->required()
						->filterable()
						->options(function(): array {
							$categories = $this->getSortedCategories();
							
							$getDepth = function(/** @var OptionCategory $category */$category) use ($categories) {
								$depth = 0;
								
								while (isset($categories[$category->parentCategoryName])) {
									$depth++;
									
									$category = $categories[$category->parentCategoryName];
								}
								
								return $depth;
							};
							
							$options = [];
							/** @var OptionCategory $category */
							foreach ($categories as $category) {
								$options[] = [
									'depth' => $getDepth($category),
									'label' => $category->categoryName,
									'value' => $category->categoryName
								];
							}
							
							return $options;
						}, true),
					
					SingleSelectionFormField::create('optionType')
						->objectProperty('optiontype')
						->label('wcf.acp.pip.abstractOption.options.optionType')
						->description('wcf.acp.pip.' . $this->tagName . '.options.optionType.description')
						->required()
						->options(function(): array {
							$classnamePieces = explode('\\', get_class());
							$pipPrefix = str_replace('OptionPackageInstallationPlugin', '', $classnamePieces);
							
							$options = [];
							
							// consider all applications for potential object types
							foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
								$optionDir = $application->getPackage()->getAbsolutePackageDir() . 'lib/system/option';
								$directoryUtil = DirectoryUtil::getInstance($optionDir);
								
								foreach ($directoryUtil->getFileObjects() as $fileObject) {
									if ($fileObject->isFile()) {
										$optionTypePrefix = '';
										
										// determine additional sub-namespace
										// example: `user` in `wcf\system\option\user`
										$additionalSubNamespace = ltrim(str_replace([$optionDir, '/'], ['', '\\'], $fileObject->getPath()), '\\');
										if ($additionalSubNamespace !== '') {
											$optionTypePrefix = implode('', array_map('ucfirst', explode('\\', $additionalSubNamespace)));
											
											// ignore additional sub-namespaced option types if they do not
											// belong to the pip
											if ($optionTypePrefix !== $pipPrefix) {
												continue;
											}
										}
										
										$namespace = $application->getAbbreviation() . '\\system\\option' . str_replace([$optionDir, '/'], ['', '\\'], $fileObject->getPath());
										$unqualifiedClassname = str_replace('.class.php', '', $fileObject->getFilename());
										$classname = $namespace . '\\' . $unqualifiedClassname;
										
										if (!is_subclass_of($classname, IOptionType::class) || !(new \ReflectionClass($classname))->isInstantiable()) {
											continue;
										}
										
										$optionType = str_replace( $optionTypePrefix . 'OptionType.class.php', '', $fileObject->getFilename());
										
										// only make first letter lowercase if the first two letters are not uppercase
										// relevant cases: `URL` and the `WBB` prefix
										if (!preg_match('~^[A-Z]{2}~', $optionType)) {
											$optionType = lcfirst($optionType);
										}
										
										if (is_subclass_of($classname, II18nOptionType::class)) {
											$this->i18nOptionTypes[] = $optionType;
										}
										
										if (is_subclass_of($classname, ISelectOptionOptionType::class)) {
											$this->selectOptionOptionTypes[] = $optionType;
										}
										
										$options[] = $optionType;
									}
								}
							}
							
							natcasesort($options);
							
							return array_combine($options, $options);
						}),
					
					MultilineTextFormField::create('defaultValue')
						->objectProperty('defaultvalue')
						->label('wcf.acp.pip.abstractOption.options.defaultValue')
						->description('wcf.acp.pip.abstractOption.options.defaultValue.description')
						->rows(5),
					
					TextFormField::create('validationPattern')
						->objectProperty('validationpattern')
						->label('wcf.acp.pip.abstractOption.options.validationPattern')
						->description('wcf.acp.pip.abstractOption.options.validationPattern.description')
						->addValidator(new FormFieldValidator('regex', function(TextFormField $formField) {
							if ($formField->getSaveValue() !== '') {
								if (!Regex::compile($formField->getSaveValue())->isValid()) {
									$formField->addValidationError(
										new FormFieldValidationError(
											'invalid',
											'wcf.acp.pip.abstractOption.options.validationPattern.error.invalid'
										)
									);
								}
							}
						})),
					
					MultilineTextFormField::create('enableOptions')
						->objectProperty('enableoptions')
						->label('wcf.acp.pip.abstractOption.options.enableOptions')
						->description('wcf.acp.pip.abstractOption.options.enableOptions.description')
						->rows(5),
					
					IntegerFormField::create('showOrder')
						->objectProperty('showorder')
						->label('wcf.acp.pip.abstractOption.options.showOrder')
						->description('wcf.acp.pip.abstractOption.options.showOrder.description'),
					
					OptionFormField::create()
						->description('wcf.acp.pip.abstractOption.options.options.description')
						->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
						->packageIDs(array_merge(
							[$this->installation->getPackage()->packageID],
							array_keys($this->installation->getPackage()->getAllRequiredPackages())
						)),
					
					UserGroupOptionFormField::create()
						->description('wcf.acp.pip.abstractOption.options.permissions.description')
						->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
						->packageIDs(array_merge(
							[$this->installation->getPackage()->packageID],
							array_keys($this->installation->getPackage()->getAllRequiredPackages())
						))
				]);
				
				/** @var SingleSelectionFormField $optionType */
				$optionType = $form->getNodeById('optionType');
				
				// add option-specific fields
				$dataContainer->appendChildren([
					IntegerFormField::create('minValue')
						->objectProperty('minvalue')
						->label('wcf.acp.pip.abstractOption.options.optionType.integer.minValue')
						->nullable()
						->addDependency(
							ValueFormFieldDependency::create('optionType')
								->field($optionType)
								->values(['integer'])
						),
					
					IntegerFormField::create('maxValue')
						->objectProperty('maxvalue')
						->label('wcf.acp.pip.abstractOption.options.optionType.integer.maxValue')
						->nullable()
						->addDependency(
							ValueFormFieldDependency::create('optionType')
								->field($optionType)
								->values(['integer'])
						),
					
					TextFormField::create('suffix')
						->label('wcf.acp.pip.abstractOption.options.optionType.integer.suffix')
						->description('wcf.acp.pip.abstractOption.options.optionType.integer.suffix.description')
						->addDependency(
							ValueFormFieldDependency::create('optionType')
								->field($optionType)
								->values(['integer'])
						),
					
					IntegerFormField::create('minLength')
						->objectProperty('minlength')
						->label('wcf.acp.pip.abstractOption.options.optionType.text.minLength')
						->minimum(0)
						->nullable()
						->addDependency(
							ValueFormFieldDependency::create('optionType')
								->field($optionType)
								->values(['password', 'text', 'textarea', 'URL'])
						),
					
					IntegerFormField::create('maxLength')
						->objectProperty('maxlength')
						->label('wcf.acp.pip.abstractOption.options.optionType.text.maxLength')
						->minimum(1)
						->nullable()
						->addDependency(
							ValueFormFieldDependency::create('optionType')
								->field($optionType)
								->values(['password', 'text', 'textarea', 'URL'])
						),
					
					BooleanFormField::create('isSortable')
						->objectProperty('issortable')
						->label('wcf.acp.pip.abstractOption.options.optionType.useroptions.isSortable')
						->description('wcf.acp.pip.abstractOption.options.optionType.useroptions.isSortable.description')
						->addDependency(
							ValueFormFieldDependency::create('optionType')
								->field($optionType)
								->values(['useroptions'])
						),
					
					BooleanFormField::create('allowEmptyValue')
						->objectProperty('allowemptyvalue')
						->label('wcf.acp.pip.abstractOption.options.optionType.select.allowEmptyValue')
						->description('wcf.acp.pip.abstractOption.options.optionType.select.allowEmptyValue.description')
						->addDependency(
							ValueFormFieldDependency::create('optionType')
								->field($optionType)
								->values(['captchaSelect'])
						),
					
					BooleanFormField::create('allowEmptyValue_select')
						->objectProperty('allowEmptyValue')
						->label('wcf.acp.pip.abstractOption.options.optionType.select.allowEmptyValue')
						->description('wcf.acp.pip.abstractOption.options.optionType.select.allowEmptyValue.description')
						->addDependency(
							ValueFormFieldDependency::create('optionType')
								->field($optionType)
								->values(['select'])
						),
				]);
				
				break;
			
			default:
				throw new \LogicException('Unreachable');
		}
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getElementData(\DOMElement $element, $saveData = false) {
		$data = [
			'packageID' => $this->installation->getPackage()->packageID
		];
		
		switch ($this->entryType) {
			case 'categories':
				$data['categoryName'] = $element->getAttribute('name');
				
				$parent = $element->getElementsByTagName('parent')->item(0);
				if ($parent !== null) {
					$data['parentCategoryName'] = $parent->nodeValue;
				}
				
				foreach (['options', 'permissions'] as $optionalPropertyName) {
					$optionalProperty = $element->getElementsByTagName($optionalPropertyName)->item(0);
					if ($optionalProperty !== null) {
						$data[$optionalPropertyName] = StringUtil::normalizeCsv($optionalProperty->nodeValue);
					}
				}
				
				$showOrder = $element->getElementsByTagName('showorder')->item(0);
				if ($showOrder !== null) {
					$data['showOrder'] = $showOrder->nodeValue;
				}
				
				break;
			
			case 'options':
				$data['optionName'] = $element->getAttribute('name');
				$data['categoryName'] = $element->getElementsByTagName('categoryname')->item(0)->nodeValue;
				$data['optionType'] = $element->getElementsByTagName('optiontype')->item(0)->nodeValue;
				
				foreach (['defaultValue', 'enableOptions', 'validationPattern', 'showOrder'] as $optionalPropertyName) {
					$optionalProperty = $element->getElementsByTagName(strtolower($optionalPropertyName))->item(0);
					if ($optionalProperty !== null) {
						$data[$optionalPropertyName] = $optionalProperty->nodeValue;
					}
				}
				
				foreach (['options', 'permissions'] as $optionalPropertyName) {
					$optionalProperty = $element->getElementsByTagName($optionalPropertyName)->item(0);
					if ($optionalProperty !== null) {
						$data[$optionalPropertyName] = StringUtil::normalizeCsv($optionalProperty->nodeValue);
					}
				}
				
				// object-type specific elements
				$optionals = [
					'minvalue',
					'maxvalue',
					'suffix',
					'minlength',
					'maxlength',
					'issortable',
					'allowemptyvalue',
				];
				
				foreach ($optionals as $optionalPropertyName) {
					$optionalProperty = $element->getElementsByTagName($optionalPropertyName)->item(0);
					if ($optionalProperty !== null) {
						$data[$optionalPropertyName] = $optionalProperty->nodeValue;
					}
				}
				
				break;
			
			default:
				throw new \LogicException('Unreachable');
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
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getEntryTypes() {
		return ['options', 'categories'];
	}
	
	/**
	 * Returns a list of sorted categories with array keys being the category names.
	 * 
	 * @return	DatabaseObject[]
	 */
	public function getSortedCategories() {
		$optionHandler = $this->getSortOptionHandler();
		if ($optionHandler === null) {
			throw new \LogicException("Missing option handler");
		}
		$optionHandler->init();
		
		// only consider categories of relevant packages
		$vrelevantPackageIDs = array_merge(
			[$this->installation->getPackage()->packageID],
			array_keys($this->installation->getPackage()->getAllRequiredPackages())
		);
		
		$buildSortedCategories = function($parentCategories) use ($vrelevantPackageIDs, &$buildSortedCategories) {
			$categories = [];
			foreach ($parentCategories as $categoryData) {
				/** @var OptionCategory $category */
				$category = $categoryData['object'];
				
				if (in_array($category->packageID, $vrelevantPackageIDs)) {
					$categories[$category->categoryName] = $category;
					
					$categories = array_merge($categories, $buildSortedCategories($categoryData['categories']));
				}
			}
			
			return $categories;
		};
		
		return $buildSortedCategories($optionHandler->getOptionTree());
	}
	
	/**
	 * Returns an option handler used for sorting.
	 * 
	 * @return	IOptionHandler
	 * @see		OptionPackageInstallationPlugin::getSortOptionHandler()
	 */
	protected function getSortOptionHandler() {
		return null;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function saveObject(\DOMElement $newElement, \DOMElement $oldElement = null) {
		switch ($this->entryType) {
			case 'categories':
				$this->saveCategory($this->getElementData($newElement, true));
				
				break;
			
			case 'options':
				$optionData = $this->getElementData($newElement, true);
				
				$this->saveOption($optionData, $optionData['categoryName']);
				
				break;
		}
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function setEntryListKeys(IDevtoolsPipEntryList $entryList) {
		switch ($this->entryType) {
			case 'categories':
				$entryList->setKeys([
					'categoryName' => 'wcf.acp.pip.abstractOption.categories.categoryName',
					'parentCategoryName' => 'wcf.acp.pip.abstractOption.categories.parentCategoryName'
				]);
				break;
				
			case 'options':
				$entryList->setKeys([
					'optionName' => 'wcf.acp.pip.abstractOption.options.optionName',
					'categoryName' => 'wcf.acp.pip.abstractOption.options.categoryName',
					'optionType' => 'wcf.acp.pip.abstractOption.options.optionType'
				]);
				break;
				
			default:
				throw new \LogicException('Unreachable');
		}
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function sortDocument(\DOMDocument $document) {
		$this->sortImportDelete($document);
		
		// `<categories>` before `<options>`
		$compareFunction = function(\DOMElement $element1, \DOMElement $element2) {
			if ($element1->nodeName === 'categories') {
				return -1;
			}
			else if ($element2->nodeName === 'categories') {
				return 1;
			}
			
			return 0;
		};
		
		$this->sortChildNodes($document->getElementsByTagName('import'), $compareFunction);
		
		$xpath = new \DOMXPath($document);
		$xpath->registerNamespace('ns', $document->documentElement->getAttribute('xmlns'));
		
		// sort categories
		$categoryOrder = array_flip(array_keys($this->getSortedCategories()));
		
		$this->sortChildNodes(
			$xpath->query('/ns:data/ns:import/ns:categories'),
			function(\DOMElement $category1, \DOMElement $category2) use ($categoryOrder) {
				return $categoryOrder[$category1->getAttribute('name')] <=> $categoryOrder[$category2->getAttribute('name')];
			}
		);
		
		// sort options
		$this->sortChildNodes(
			$xpath->query('/ns:data/ns:import/ns:options'),
			function(\DOMElement $option1, \DOMElement $option2) use ($categoryOrder) {
				$category1 = $option1->getElementsByTagName('categoryname')->item(0)->nodeValue;
				$category2 = $option2->getElementsByTagName('categoryname')->item(0)->nodeValue;
				
				if ($category1 !== 'hidden') {
					if ($category2 !== 'hidden') {
						$cmp = $categoryOrder[$category1] <=> $categoryOrder[$category2];
						
						if ($cmp === 0) {
							$showOrder1 = $option1->getElementsByTagName('showorder')->item(0);
							$showOrder2 = $option2->getElementsByTagName('showorder')->item(0);
							
							if ($showOrder1 !== null) {
								if ($showOrder2 !== null) {
									return $showOrder1->nodeValue <=> $showOrder2->nodeValue;
								}
								
								return -1;
							}
							else if ($showOrder2 !== null) {
								return 1;
							}
							
							return strcmp($option1->nodeValue, $option2->nodeValue);
						}
						
						return $cmp;
					}
					
					return -1;
				}
				else if ($category2 !== 'hidden') {
					return 1;
				}
				
				return strcmp($option1->nodeValue, $option2->nodeValue);
			}
		);
		
		// sort deleted elements
		$this->sortChildNodes($document->getElementsByTagName('delete'), function(\DOMElement $element1, \DOMElement $element2) use ($categoryOrder) {
			if ($element1->nodeName === 'optioncategory') {
				if ($element2->nodeName === 'optioncategory') {
					return $categoryOrder[$element1->getAttribute('name')] <=> $categoryOrder[$element2->getAttribute('name')];
				}
				else {
					return -1;
				}
			}
			else if ($element2->nodeName === 'optioncategory') {
				return 1;
			}
			
			// two options
			return strcmp($element1->nodeName, $element2->nodeName);
		});
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function writeEntry(\DOMDocument $document, IFormDocument $form) {
		$formData = $form->getData()['data'];
		
		$xpath = new \DOMXPath($document);
		$xpath->registerNamespace('ns', $document->documentElement->getAttribute('xmlns'));
		
		switch ($this->entryType) {
			case 'categories':
				$category = $document->createElement('category');
				
				$document->getElementsByTagName('import')->item(0)->appendChild($category);
				
				foreach (['parent', 'showorder', 'options', 'permissions'] as $field) {
					if (isset($formData[$field]) && $formData[$field] !== '') {
						$category->appendChild($document->createElement($field, (string) $formData[$field]));
					}
				}
				
				$xpath->query('/ns:data/ns:import/ns:categories')->item(0)->appendChild($category);
				
				return $category;
			
			case 'options':
				$option = $document->createElement($this->tagName);
				$option->setAttribute('name', $formData['name']);
				
				foreach (['categoryname', 'optiontype'] as $field) {
					$option->appendChild($document->createElement($field, (string) $formData[$field]));
				}
				
				$fields = [
					'defaultvalue' => '',
					'validationpattern' => '',
					'enableoptions' => '',
					'showorder' => 0,
					'options' => '',
					'permissions' => '',
					
					// object-type specific elements
					'minvalue' => null,
					'maxvalue' => null,
					'suffix' => '',
					'minlength' => null,
					'maxlength' => null,
					'issortable' => false,
					'allowemptyvalue' => false
				];
				foreach ($fields as $field => $defaultValue) {
					if (isset($formData[$field]) && $formData[$field] !== $defaultValue) {
						$option->appendChild($document->createElement($field, (string) $formData[$field]));
					}
				}
				
				$xpath->query('/ns:data/ns:import/ns:options')->item(0)->appendChild($option);
				
				return $option;
			
			default:
				throw new \LogicException('Unreachable');
		}
	}
}
