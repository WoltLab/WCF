<?php
namespace wcf\system\package\plugin;
use wcf\data\object\type\definition\ObjectTypeDefinitionList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\object\type\ObjectTypeEditor;
use wcf\data\DatabaseObjectList;
use wcf\system\application\ApplicationHandler;
use wcf\system\condition\AbstractIntegerCondition;
use wcf\system\condition\UserGroupCondition;
use wcf\system\condition\UserIntegerPropertyCondition;
use wcf\system\condition\UserTimestampPropertyCondition;
use wcf\system\devtools\pip\DevtoolsPipEntryList;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\OptionFormField;
use wcf\system\form\builder\field\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\FloatFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;

/**
 * Installs, updates and deletes object types.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Package\Plugin
 */
class ObjectTypePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements IGuiPackageInstallationPlugin {
	use TXmlGuiPackageInstallationPlugin;
	
	/**
	 * @inheritDoc
	 */
	public $className = ObjectTypeEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public $tagName = 'type';
	
	/**
	 * list of names of tags which aren't considered as additional data
	 * @var	string[]
	 */
	public static $reservedTags = ['classname', 'definitionname', 'name'];
	
	/**
	 * @var	string[]
	 */
	public $definitionNames = [];
	
	/**
	 * @var	string[]
	 */
	public $definitionInterfaces = [];
	
	/**
	 * Returns the id of the object type definition with the given name.
	 * 
	 * @param	string		$definitionName
	 * @return	integer
	 * @throws	SystemException
	 */
	protected function getDefinitionID($definitionName) {
		// get object type id
		$sql = "SELECT	definitionID
			FROM	wcf".WCF_N."_object_type_definition
			WHERE	definitionName = ?";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([$definitionName]);
		$row = $statement->fetchArray();
		if (empty($row['definitionID'])) throw new SystemException("unknown object type definition '".$definitionName."' given");
		return $row['definitionID'];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) {
		$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
			WHERE		objectType = ?
					AND definitionID = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($items as $item) {
			$statement->execute([
				$item['attributes']['name'],
				$this->getDefinitionID($item['elements']['definitionname']),
				$this->installation->getPackageID()
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) {
		$additionalData = [];
		foreach ($data['elements'] as $tagName => $nodeValue) {
			if (!in_array($tagName, self::$reservedTags)) $additionalData[$tagName] = $nodeValue;
		}
		
		return [
			'definitionID' => $this->getDefinitionID($data['elements']['definitionname']),
			'objectType' => $data['elements']['name'],
			'className' => isset($data['elements']['classname']) ? $data['elements']['classname'] : '',
			'additionalData' => serialize($additionalData)
		];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) {
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_".$this->tableName."
			WHERE	objectType = ?
				AND definitionID = ?
				AND packageID = ?";
		$parameters = [
			$data['objectType'],
			$data['definitionID'],
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
	public static function getSyncDependencies() {
		return ['objectTypeDefinition'];
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getAdditionalTemplateCode() {
		return WCF::getTPL()->fetch('__objectTypePipGui', 'wcf', [
			'definitionNames' => $this->definitionNames,
			'definitionInterfaces' => $this->definitionInterfaces
		], true);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getElementData(\DOMElement $element, $saveData = false) {
		$data = [
			'definitionID' => $this->getDefinitionID($element->getElementsByTagName('definitionname')->item(0)->nodeValue),
			'objectType' => $element->getElementsByTagName('name')->item(0)->nodeValue,
			'packageID' => $this->installation->getPackage()->packageID
		];
		
		$className = $element->getElementsByTagName('classname')->item(0);
		if ($className) {
			$data['className'] = $className->nodeValue;
		}
		
		$additionalData = [];
		
		/** @var \DOMElement $child */
		foreach ($element->childNodes as $child) {
			if (!in_array($child->nodeName, self::$reservedTags)) {
				$additionalData[$child->nodeName] = $child->nodeValue;
			}
		}
		
		if ($saveData) {
			$data['additionalData'] = serialize($additionalData);
		}
		else {
			$data = array_merge($additionalData, $data);
		}
		
		return $data;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function addFormFields(IFormDocument $form) {
		// read available object type definitions
		$list = new ObjectTypeDefinitionList();
		$list->sqlOrderBy = 'definitionName';
		$list->readObjects();
		
		foreach ($list as $definition) {
			$this->definitionNames[$definition->definitionID] = $definition->definitionName;
			
			if ($definition->interfaceName) {
				$this->definitionInterfaces[$definition->definitionID] = $definition->interfaceName;
			}
		}
		
		// add default form fields
		/** @var FormContainer $dataContainer */
		$dataContainer = $form->getNodeById('data');
		
		$dataContainer->appendChildren([
			SingleSelectionFormField::create('definitionID')
				->label('wcf.acp.pip.objectType.definitionName')
				->description('<!-- will be replaced by JavaScript -->')
				->options($this->definitionNames)
				->required(),
			
			TextFormField::create('objectType')
				->objectProperty('name')
				->label('wcf.acp.pip.objectType.objectType')
				->description('wcf.acp.pip.objectType.objectType.description')
				->required()
				->addValidator(self::getObjectTypeAlikeValueValidator('wcf.acp.pip.objectType.objectType'))
				->addValidator(new FormFieldValidator('uniqueness', function(TextFormField $formField) {
					/** @var SingleSelectionFormField $definitionIDField */
					$definitionIDField = $formField->getDocument()->getNodeById('definitionID');
					
					$definitionID = $definitionIDField->getSaveValue();
					if ($definitionID) {
						$definition = ObjectTypeCache::getInstance()->getDefinition($definitionID);
						
						$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
							$definition->definitionName,
							$formField->getValue()
						);
						
						// the object type name is not unique if such an object type already exists
						// and (a) a new object type is added or (b) the existing object type is
						// different from the edited object type
						if ($objectType !== null && (
								$formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE ||
								$this->editedEntry->getElementsByTagName('name')->item(0)->nodeValue !== $formField->getValue() ||
								$this->editedEntry->getElementsByTagName('definitionname')->item(0)->nodeValue !== $definition->definitionName
							)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'notUnique',
									'wcf.acp.pip.objectType.objectType.error.notUnique'
								)
							);
						}
					}
				})),
			
			ClassNameFormField::create()
				->objectProperty('classname')
				->description('<!-- will be replaced by JavaScript -->')
				->required()
				->addValidator(new FormFieldValidator('implementsInterface', function(TextFormField $formField) {
					/** @var SingleSelectionFormField $definitionIDField */
					$definitionIDField = $formField->getDocument()->getNodeById('definitionID');
					
					$definitionID = $definitionIDField->getSaveValue();
					if ($definitionID) {
						$definition = ObjectTypeCache::getInstance()->getDefinition($definitionID);
						
						if (!is_subclass_of($formField->getValue(), $definition->interfaceName)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'interface',
									'wcf.form.field.className.error.interface',
									['interface' => $definition->interfaceName]
								)
							);
						}
					}
				})),
		]);
		
		/** @var SingleSelectionFormField $definitionName */
		$definitionID = $form->getNodeById('definitionID');
		
		// add general field dependencies
		$form->getNodeById('className')->addDependency(
			ValueFormFieldDependency::create('definitionID')
				->field($definitionID)
				->values(array_keys($this->definitionInterfaces))
		);
		
		// add object type-specific fields
		
		// com.woltlab.wcf.adLocation
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.adLocation')
			->appendChildren([
				TextFormField::create('adLocationCategoryName')
					->objectProperty('categoryname')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.categoryName')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.categoryName.description')
					->addValidator(self::getObjectTypeAlikeValueValidator('wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.categoryName')),
				ItemListFormField::create('adLocationCssClassName')
					->objectProperty('cssclassname')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.cssClassName')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.cssClassName.description')
					->saveValueType(ItemListFormField::SAVE_VALUE_TYPE_SSV)
					->addValidator(new FormFieldValidator('format', function(ItemListFormField $formField) {
						if (!empty($formField->getValue())) {
							$invalidClasses = [];
							foreach ($formField->getValue() as $class) {
								if (preg_match('~^-?[_A-z][_A-z0-9-]*$~', $class) !== 1) {
									$invalidClasses[] = $class;
								}
							}
							
							if (!empty($invalidClasses)) {
								$formField->addValidationError(
									new FormFieldValidationError(
										'invalid',
										'wcf.acp.pip.objectType.com.woltlab.wcf.adLocation.cssClassName.error.invalid',
										['invalidClasses' => $invalidClasses]
									)
								);
							}
						}
					}))
			]);
		
		// com.woltlab.wcf.attachment.objectType
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.attachment.objectType')
			->appendChild(
				BooleanFormField::create('attachmentPrivate')
					->objectProperty('private')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.attachment.objectType.private')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.attachment.objectType.private.description')
			);
		
		// com.woltlab.wcf.bulkProcessing.user.action
		$this->addBulkProcessingActionFields($form, 'com.woltlab.wcf.bulkProcessing.user.action');
		
		// com.woltlab.wcf.bulkProcessing.user.condition
		$bulkProcessingUserConditionContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.bulkProcessing.user.condition');
		$this->addConditionFields($bulkProcessingUserConditionContainer);
		
		// com.woltlab.wcf.category
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.category')
			->appendChild(
				BooleanFormField::create('categoryDefaultPermission')
					->objectProperty('defaultpermission')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.category.defaultPermission')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.category.defaultPermission.description')
			);
		
		// com.woltlab.wcf.clipboardItem
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.clipboardItem')
			->appendChild(
				ClassNameFormField::create('clipboardItemListClassName')
					->objectProperty('listclassname')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.clipboardItem.listClassName')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.clipboardItem.listClassName.description')
					->required()
					->parentClass(DatabaseObjectList::class)
			);
		
		// com.woltlab.wcf.condition.ad
		$conditionAdContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.condition.ad');
		$this->addConditionFields($conditionAdContainer, true, false);
		
		// com.woltlab.wcf.condition.notice
		$conditionAdContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.condition.notice');
		$this->addConditionFields($conditionAdContainer);
		
		// com.woltlab.wcf.condition.trophy
		$conditionAdContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.condition.trophy');
		$this->addConditionFields($conditionAdContainer, false, true);
		
		// com.woltlab.wcf.condition.userGroupAssignment
		$conditionAdContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.condition.userGroupAssignment');
		$this->addConditionFields($conditionAdContainer, false, true);
		
		// com.woltlab.wcf.condition.userSearch
		$conditionAdContainer = $this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.condition.userSearch');
		$this->addConditionFields($conditionAdContainer, false, true);
		
		// com.woltlab.wcf.notification.objectType
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.notification.objectType')
			->appendChildren([
				TextFormField::create('notificationObjectTypeCategory')
					->objectProperty('category')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.notification.objectType.category')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.notification.objectType.category.description'),
					// TODO: validator
				
				BooleanFormField::create('notificationObjectTypeSupportsReactions')
					->objectProperty('supportsReactions')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.notification.objectType.supportsReactions')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.notification.objectType.supportsReactions.description')
			]);
		
		// com.woltlab.wcf.rebuildData
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.rebuildData')
			->appendChild(
				IntegerFormField::create('rebuildDataNiceValue')
					->objectProperty('nicevalue')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.rebuildData.niceValue')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.rebuildData.niceValue.description')
					->nullable()
			);
		
		// com.woltlab.wcf.searchableObjectType
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.searchableObjectType')
			->appendChild(
				TextFormField::create('searchableObjectTypeSearchIndex')
					->objectProperty('searchindex')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.searchableObjectType.searchIndex')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.searchableObjectType.searchIndex.description')
					->required()
					->addValidator(new FormFieldValidator('tableName', function(TextFormField $formField) {
						if ($formField->getValue()) {
							if (preg_match('~^(?P<app>[A-z]+)1_[A-z_]+$~', $formField->getValue(), $match)) {
								if (!ApplicationHandler::getInstance()->getApplication($match['app'])) {
									$formField->addValidationError(
										new FormFieldValidationError(
											'unknownApp',
											'wcf.acp.pip.objectType.com.woltlab.wcf.searchableObjectType.searchIndex.error.unknownApp',
											['app' => $match['app']]
										)
									);
								}
							}
							else {
								$formField->addValidationError(
									new FormFieldValidationError(
										'invalid',
										'wcf.acp.pip.objectType.com.woltlab.wcf.searchableObjectType.searchIndex.error.invalid'
									)
								);
							}
						}
					}))
			);
		
		// com.woltlab.wcf.sitemap.object
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.sitemap.object')
			->appendChildren([
				FloatFormField::create('sitemapObjectPriority')
					->objectProperty('priority')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.priority')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.priority.description')
					->required()
					->minimum(0.0)
					->maximum(1.0)
					->step(0.1)
					->value(0.5),
				
				SingleSelectionFormField::create('sitemapObjectchangeFreq')
					->objectProperty('changeFreq')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.changeFreq')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.changeFreq.description')
					->options([
						'always',
						'hourly',
						'daily',
						'weekly',
						'monthly',
						'yearly',
						'never'
					])
					->required(),
				
				IntegerFormField::create('sitemapObjectRebuildTime')
					->objectProperty('rebuildTime')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.rebuildTime')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.sitemap.object.rebuildTime.description')
					->suffix('wcf.acp.option.suffix.seconds')
					->required()
					->minimum(0)
			]);
		
		// com.woltlab.wcf.statDailyHandler
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.statDailyHandler')
			->appendChildren([
				TextFormField::create('statDailyHandlerCategoryName')
					->objectProperty('categoryname')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.statDailyHandler.categoryName')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.statDailyHandler.categoryName.description')
					->addValidator(self::getObjectTypeAlikeValueValidator('wcf.acp.pip.objectType.com.woltlab.wcf.statDailyHandler.categoryName')),
				
				BooleanFormField::create('statDailyHandlerIsDefault')
					->objectProperty('default')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.statDailyHandler.isDefault')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.statDailyHandler.isDefault.description')
			]);
		
		// com.woltlab.wcf.tagging.taggableObject
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.tagging.taggableObject')
			->appendChildren([
				OptionFormField::create('taggingTaggableObjectOptions')
					->objectProperty('options')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.tagging.taggableObject.options.description')
					->packageIDs(array_merge(
						[$this->installation->getPackage()->packageID],
						array_keys($this->installation->getPackage()->getAllRequiredPackages())
					)),
				
				UserGroupOptionFormField::create('taggingTaggableObjectPermissions')
					->objectProperty('permissions')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.tagging.taggableObject.permissions.description')
					->packageIDs(array_merge(
						[$this->installation->getPackage()->packageID],
						array_keys($this->installation->getPackage()->getAllRequiredPackages())
					))
			]);
		
		// com.woltlab.wcf.user.activityPointEvent
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.user.activityPointEvent')
			->appendChild(
				IntegerFormField::create('userActivityPointEventPoints')
					->objectProperty('points')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.user.activityPointEvent.points')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.user.activityPointEvent.points.description')
					->minimum(0)
					->required()
			);
		
		// com.woltlab.wcf.user.recentActivityEvent
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.user.recentActivityEvent')
			->appendChild(
				BooleanFormField::create('userRecentActivityEventSupportsReactions')
					->objectProperty('supportsReactions')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.user.recentActivityEvent.supportsReactions')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.user.recentActivityEvent.supportsReactions.description')
			);
		
		// com.woltlab.wcf.versionTracker.objectType
		$this->getObjectTypeDefinitionDataContainer($form, 'com.woltlab.wcf.versionTracker.objectType')
			->appendChildren([
				TextFormField::create('versionTrackerObjectTypeTableName')
					->objectProperty('tableName')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tableName')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tableName.description')
					->required()
					->addValidator(new FormFieldValidator('tableExists', function(TextFormField $formField) {
						if ($formField->getValue()) {
							$value = ApplicationHandler::insertRealDatabaseTableNames($formField->getValue());
							
							if (!in_array($value, WCF::getDB()->getEditor()->getTableNames())) {
								$formField->addValidationError(new FormFieldValidationError(
									'nonExistent',
									'wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tableName.error.nonExistent',
									['tableName' => $value]
								));
							}
						}
					})),
				
				TextFormField::create('versionTrackerObjectTypeTablePrimaryKey')
					->objectProperty('tablePrimaryKey')
					->label('wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tablePrimaryKey')
					->description('wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tablePrimaryKey.description')
					->required()
					->addValidator(new FormFieldValidator('columnExists', function(TextFormField $formField) {
						if ($formField->getValue()) {
							/** @var TextFormField $tableName */
							$tableName = $formField->getDocument()->getNodeById('versionTrackerObjectTypeTableName');
							
							if (empty($tableName->getValidationErrors())) {
								// table name has already been validated and table exists
								$columns = WCF::getDB()->getEditor()->getColumns($tableName->getValue());
								
								foreach ($columns as $column) {
									if ($column['name'] === $formField->getValue()) {
										if ($column['data']['key'] !== 'PRIMARY') {
											$formField->addValidationError(new FormFieldValidationError(
												'noPrimaryColumn',
												'wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tablePrimaryKey.error.noPrimaryColumn'
											));
										}
										
										return;
									}
								}
								
								$formField->addValidationError(new FormFieldValidationError(
									'nonExistent',
									'wcf.acp.pip.objectType.com.woltlab.wcf.versionTracker.objectType.tablePrimaryKey.error.nonExistent'
								));
							}
						}
					})),
			]);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getElementIdentifier(\DOMElement $element) {
		return sha1(
			$element->getElementsByTagName('name')->item(0)->nodeValue . '/' .
			$element->getElementsByTagName('definitionname')->item(0)->nodeValue
		);
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function getEmptyXml() {
		return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<data xmlns="http://www.woltlab.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.woltlab.com http://www.woltlab.com/XSD/vortex/objectType.xsd">
	<import></import>
</data>
XML;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public function getEntryList() {
		$xml = $this->getProjectXml();
		$xpath = $xml->xpath();
		
		$entryList = new DevtoolsPipEntryList();
		$this->setEntryListKeys($entryList);
		
		/** @var \DOMElement $element */
		foreach ($this->getImportElements($xpath) as $element) {
			$entryList->addEntry($this->getElementIdentifier($element), [
				'name' => $element->getElementsByTagName('name')->item(0)->nodeValue,
				'definitionName' => $element->getElementsByTagName('definitionname')->item(0)->nodeValue
			]);
		}
		
		return $entryList;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function setEntryListKeys(IDevtoolsPipEntryList $entryList) {
		$entryList->setKeys([
			'name' => 'wcf.acp.pip.objectType.objectType',
			'definitionName' => 'wcf.acp.pip.objectType.definitionName'
		]);
	}
	
	/**
	 * Returns a form container for the object type definition-specific fields
	 * of the the object type definition with the given name.
	 * 
	 * The returned form container is already appended to the given form and
	 * has a dependency on the `definitionName` field so that the form container
	 * is only shown for the relevant object type definition.
	 * 
	 * @param	IFormDocument	$form
	 * @param	string		$definitionName
	 * @return	FormContainer
	 * @since	3.2
	 */
	public function getObjectTypeDefinitionDataContainer(IFormDocument $form, $definitionName) {
		/** @var SingleSelectionFormField $definitionNameField */
		$definitionIDField = $form->getNodeById('definitionID');
		
		$definitionPieces = explode('.', $definitionName);
		
		$formContainer = FormContainer::create(lcfirst(implode('', array_map('ucfirst', $definitionPieces))) . 'Fields')
			->label('wcf.acp.pip.objectType.' . $definitionName . '.data.title')
			->addDependency(
				ValueFormFieldDependency::create('definitionID')
					->field($definitionIDField)
					->values([ObjectTypeCache::getInstance()->getDefinitionByName($definitionName)->definitionID])
			);
		
		$form->appendChild($formContainer);
		
		return $formContainer;
	}
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	protected function createXmlElement(\DOMDocument $document, IFormDocument $form) {
		$type = $document->createElement($this->tagName);
		foreach ($form->getData()['data'] as $key => $value) {
			if ($key === 'definitionID') {
				$key = 'definitionname';
				$value = ObjectTypeCache::getInstance()->getDefinition($value)->definitionName;
			}
			
			if ($value !== '') {
				if (is_string($value)) {
					$type->appendChild($document->createElement($key, $this->getAutoCdataValue($value)));
				}
				else {
					$type->appendChild($document->createElement($key, (string) $value));
				}
			}
		}
		
		return $type;
	}
	
	/**
	 * Adds bulk processing action-related fields to the given form for the given bulk
	 * processing action object type definition. 
	 * 
	 * @param	IFormDocument	$form
	 * @param	string		$objectTypeDefinition
	 */
	public function addBulkProcessingActionFields(IFormDocument $form, $objectTypeDefinition) {
		$definitionPieces = explode('.', $objectTypeDefinition);
		$definitionIdString = implode('', array_map('ucfirst', $definitionPieces));
		
		$this->getObjectTypeDefinitionDataContainer($form, $objectTypeDefinition)
			->appendChildren([
				TextFormField::create('bulkProcessing' . $definitionIdString . 'Action')
					->objectProperty('action')
					->label('wcf.acp.pip.objectType.bulkProcessing.action')
					->description('wcf.acp.pip.objectType.bulkProcessing.action.description')
					->addValidator(new FormFieldValidator('format', function(TextFormField $formField) {
						if (!preg_match('~^[a-z][A-z]+$~', $formField->getValue())) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'format',
									'wcf.acp.pip.objectType.bulkProcessing.action.error.format'
								)
							);
						}
					})),
				
				OptionFormField::create('bulkProcessing' . $definitionIdString . 'Options')
					->objectProperty('options')
					->description('wcf.acp.pip.objectType.bulkProcessing.action.options.description')
					->packageIDs(array_merge(
						[$this->installation->getPackage()->packageID],
						array_keys($this->installation->getPackage()->getAllRequiredPackages())
					)),
				
				UserGroupOptionFormField::create('bulkProcessing' . $definitionIdString . 'Permissions')
					->objectProperty('permissions')
					->description('wcf.acp.pip.objectType.bulkProcessing.action.permissions.description')
					->packageIDs(array_merge(
						[$this->installation->getPackage()->packageID],
						array_keys($this->installation->getPackage()->getAllRequiredPackages())
					))
			]);
	}
	
	/**
	 * Adds all condition specific fields to the given form container.
	 * 
	 * @param	IFormContainer		$dataContainer
	 * @param	bool			$addConditionObject
	 * @param	bool			$addConditionGroup
	 * @since	3.2
	 */
	public function addConditionFields(IFormContainer $dataContainer, $addConditionObject = true, $addConditionGroup = true) {
		$prefix = preg_replace('~Fields$~', '', $dataContainer->getId());
		
		if ($addConditionObject) {
			$dataContainer->appendChild(
				TextFormField::create($prefix . 'ConditionObject')
					->objectProperty('conditionobject')
					->label('wcf.acp.pip.objectType.condition.conditionObject')
					->description('wcf.acp.pip.objectType.condition.conditionObject.description')
					->required()
					->addValidator(self::getObjectTypeAlikeValueValidator('wcf.acp.pip.objectType.condition.conditionObject'))
			);
		}
		
		if ($addConditionGroup) {
			$dataContainer->appendChild(
				TextFormField::create($prefix . 'ConditionGroup')
					->objectProperty('conditiongroup')
					->label('wcf.acp.pip.objectType.condition.conditionGroup')
					->description('wcf.acp.pip.objectType.condition.conditionGroup.description')
					->addValidator(new FormFieldValidator('format', function(TextFormField $formField) {
						if ($formField->getValue() && !preg_match('~^[a-z][A-z]+$~', $formField->getValue())) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'format',
									'wcf.acp.pip.objectType.condition.conditionGroup.error.format'
								)
							);
						}
					}))
			);
		}
		
		// classes extending `AbstractIntegerCondition`
		$integerConditions = [];
		foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
			$conditionDir = $application->getPackage()->getAbsolutePackageDir() . 'lib/system/condition/';
			
			if (file_exists($conditionDir)) {
				$directory = DirectoryUtil::getInstance($conditionDir);
				$conditionList = $directory->getFiles(SORT_ASC, new Regex('Condition\.class\.php$'));
				
				/** @var string $condition */
				foreach ($conditionList as $condition) {
					$pathPieces = explode('/', str_replace($conditionDir, '', $condition));
					$filename = array_pop($pathPieces);
					
					$className = $application->getAbbreviation() . '\system\condition\\';
					if (!empty($pathPieces)) {
						$className .= implode('\\', $pathPieces) . '\\';
					}
					$className .= basename($filename, '.class.php');
					if (class_exists($className) && is_subclass_of($className, AbstractIntegerCondition::class)) {
						$reflection = new \ReflectionClass($className);
						if ($reflection->isInstantiable()) {
							$integerConditions[] = $className;
						}
					}
				}
			}
		}
		
		/** @var TextFormField $className */
		$className = $dataContainer->getDocument()->getNodeById('className');
		
		// `UserGroupCondition`
		$dataContainer->appendChild(
			BooleanFormField::create($prefix . 'UserGroupIncludeGuests')
				->objectProperty('includeguests')
				->label('wcf.acp.pip.objectType.condition.userGroup.includeGuests')
				->description('wcf.acp.pip.objectType.condition.userGroup.includeGuests.description')
				->addDependency(
					ValueFormFieldDependency::create('className')
						->field($className)
						->values([UserGroupCondition::class])
				)
		);
		
		// `UserIntegerPropertyCondition`
		$dataContainer->appendChild(
			$this->getIntegerConditionPropertyNameField(
				$className,
				UserIntegerPropertyCondition::class,
				$prefix . 'UserIntegerPropertyName',
				'wcf.acp.pip.objectType.condition.userIntegerProperty',
				'wcf' . WCF_N . '_user'
			)->required()
		);
		
		// `UserTimestampPropertyCondition`
		$dataContainer->appendChild(
			$this->getIntegerConditionPropertyNameField(
				$className,
				UserTimestampPropertyCondition::class,
				$prefix . 'UserTimestampPropertyName',
				'wcf.acp.pip.objectType.condition.userIntegerProperty',
				'wcf' . WCF_N . '_user'
			)->required()
		);
		
		$parameters = [
			'dataContainer' => $dataContainer,
			'prefix' => $prefix
		];
		EventHandler::getInstance()->fireAction($this, 'addConditionFields', $parameters);
		
		// integer property fields should be shown last
		$dataContainer->appendChildren([
			IntegerFormField::create($prefix . 'IntegerMinValue')
				->objectProperty('minvalue')
				->label('wcf.acp.pip.objectType.condition.integer.minValue')
				->description('wcf.acp.pip.objectType.condition.integer.minValue.description')
				->addDependency(
					ValueFormFieldDependency::create('className')
						->field($className)
						->values($integerConditions)
				),
			IntegerFormField::create($prefix . 'IntegerMaxValue')
				->objectProperty('maxvalue')
				->label('wcf.acp.pip.objectType.condition.integer.maxValue')
				->description('wcf.acp.pip.objectType.condition.integer.maxValue.description')
				->addDependency(
					ValueFormFieldDependency::create('className')
						->field($className)
						->values($integerConditions)
				)
		]);
	}
	
	/**
	 * Returns a form field to enter the name of an integer property for an
	 * integer condition.
	 * 
	 * TODO: Can be use generic language items instead?
	 * The following language items must exist:
	 * 	- `{$languageItemPrefix}.propertyName`,
	 * 	- `{$languageItemPrefix}.propertyName.description`,
	 * 	- `{$languageItemPrefix}.propertyName.error.noIntegerColumn`,
	 * 	- `{$languageItemPrefix}.propertyName.error.nonExistent`.
	 * 
	 * @param	TextFormField	$classNameField		class name field on which the visibility of the created field depends
	 * @param	string		$conditionClass		name of the PHP class the field is created for
	 * @param	string		$id			id of the created field
	 * @param	string		$languageItemPrefix	language item prefix used for label, description, and error language items
	 * @param	string		$databaseTableName	name of the database table that stores the conditioned objects
	 * @return	TextFormField
	 */
	public function getIntegerConditionPropertyNameField(TextFormField $classNameField, $conditionClass, $id, $languageItemPrefix, $databaseTableName) {
		return TextFormField::create($id)
			->objectProperty('propertyname')
			->label($languageItemPrefix . '.propertyName')
			->description($languageItemPrefix . '.propertyName.description')
			->addDependency(
				ValueFormFieldDependency::create('className')
					->field($classNameField)
					->values([$conditionClass])
			)
			->addValidator(new FormFieldValidator('userTableIntegerColumn', function(TextFormField $formField) use ($databaseTableName, $languageItemPrefix) {
				if ($formField->getSaveValue()) {
					$columns = WCF::getDB()->getEditor()->getColumns($databaseTableName);
					
					foreach ($columns as $column) {
						if ($column['name'] === $formField->getValue()) {
							if ($column['data']['type'] !== 'int') {
								$formField->addValidationError(new FormFieldValidationError(
									'noIntegerColumn',
									$languageItemPrefix . '.propertyName.error.noIntegerColumn'
								));
							}
							
							return;
						}
					}
					
					$formField->addValidationError(new FormFieldValidationError(
						'nonExistent',
						$languageItemPrefix . '.propertyName.error.nonExistent'
					));
				}
			}));
	}
	
	/**
	 * Returns a form field validator to validate a string value that has a
	 * object type-alike structure.
	 * 
	 * @param	string		$languageItemPrefix	used for error language items: `{$languageItemPrefix}.error.{errorType}`
	 * @param	int		$minimumSegmentCount	minimum number of dot-separated segments
	 * @return	FormFieldValidator
	 */
	public static function getObjectTypeAlikeValueValidator($languageItemPrefix, $minimumSegmentCount = 4) {
		return new FormFieldValidator('format', function(TextFormField $formField) use ($languageItemPrefix, $minimumSegmentCount) {
			if ($formField->getValue()) {
				$segments = explode('.', $formField->getValue());
				if (count($segments) < $minimumSegmentCount) {
					$formField->addValidationError(
						new FormFieldValidationError(
							'tooFewSegments',
							$languageItemPrefix . '.error.tooFewSegments',
							['segmentCount' => count($segments)]
						)
					);
				}
				else {
					$invalidSegments = [];
					foreach ($segments as $key => $segment) {
						if (!preg_match('~^[A-z0-9\-\_]+$~', $segment)) {
							$invalidSegments[$key] = $segment;
						}
					}
					
					if (!empty($invalidSegments)) {
						$formField->addValidationError(
							new FormFieldValidationError(
								'invalidSegments',
								$languageItemPrefix . '.error.invalidSegments',
								['invalidSegments' => $invalidSegments]
							)
						);
					}
				}
			}
		});
	}
}
