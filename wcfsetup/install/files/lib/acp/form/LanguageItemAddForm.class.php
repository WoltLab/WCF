<?php
namespace wcf\acp\form;
use wcf\data\language\category\LanguageCategoryList;
use wcf\data\language\item\LanguageItemAction;
use wcf\data\language\item\LanguageItemList;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\data\processor\CustomFormFieldDataProcessor;
use wcf\system\form\builder\field\data\processor\VoidFormFieldDataProcessor;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\LanguageFactory;

/**
 * Shows the form to create a new language item.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.2
 */
class LanguageItemAddForm extends AbstractFormBuilderForm {
	/**
	 * @inheritDoc
	 */
	public $formAction = 'create';
	
	/**
	 * @inheritDoc
	 */
	public $objectActionClass = LanguageItemAction::class;
	
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.item.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	public $objectActionName = 'createLanguageItems';
	
	/**
	 * @inheritDoc
	 */
	protected function createForm() {
		parent::createForm();
		
		$dataContainer = FormContainer::create('data')
			->label('wcf.global.form.data')
			->appendChildren([
				RadioButtonFormField::create('languageCategoryIDMode')
					->label('wcf.acp.language.item.languageCategoryID.mode')
					->options([
						'automatic' => 'wcf.acp.language.item.languageCategoryID.mode.automatic',
						'selection' => 'wcf.acp.language.item.languageCategoryID.mode.selection'
					])
					->value('automatic'),
				
				SingleSelectionFormField::create('languageCategoryID')
					->label('wcf.acp.language.item.languageCategoryID')
					->description('wcf.acp.language.item.languageCategoryID.description')
					->options(function() {
						$list = new LanguageCategoryList();
						$list->sqlOrderBy = 'languageCategory ASC';
						
						return $list;
					}, false, false)
					->filterable(),
				
				TextFormField::create('languageItem')
					->label('wcf.acp.language.item.languageItem')
					->description('wcf.acp.language.item.languageItem.description')
					->required()
					->maximumLength(191)
					->addValidator(new FormFieldValidator('format', function(TextFormField $formField) {
						if (!preg_match('~^[A-z0-9-_]+(\.[A-z0-9-_]+){2,}$~', $formField->getSaveValue(), $m)) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'format',
									'wcf.acp.language.item.languageItem.error.format'
								)
							);
						}
					}))
					->addValidator(new FormFieldValidator('languageCategory', function(TextFormField $formField) {
						/** @var RadioButtonFormField $languageCategoryIDMode */
						$languageCategoryIDMode = $formField->getDocument()->getNodeById('languageCategoryIDMode');
						
						switch ($languageCategoryIDMode->getSaveValue()) {
							case 'automatic':
								$languageItemPieces = explode('.', $formField->getSaveValue());
								
								$category = LanguageFactory::getInstance()->getCategory(
									$languageItemPieces[0] . '.' . $languageItemPieces[1] . '.' . $languageItemPieces[2]
								);
								if ($category === null) {
									$category = LanguageFactory::getInstance()->getCategory(
										$languageItemPieces[0] . '.' . $languageItemPieces[1]
									);
								}
								
								if ($category === null) {
									$languageCategoryIDMode->addValidationError(
										new FormFieldValidationError(
											'automatic',
											'wcf.acp.language.item.languageCategoryID.mode.error.automaticImpossible'
										)
									);
								}
								
								break;
								
							case 'selection':
								/** @var SingleSelectionFormField $languageCategoryID */
								$languageCategoryID = $formField->getDocument()->getNodeById('languageCategoryID');
								
								$languageCategory = LanguageFactory::getInstance()->getCategoryByID($languageCategoryID->getSaveValue());
								
								if (strpos($formField->getSaveValue(), $languageCategory->languageCategory) . '.' !== 0) {
									$formField->addValidationError(
										new FormFieldValidationError(
											'prefixMismatch',
											'wcf.acp.language.item.languageItem.error.prefixMismatch'
										)
									);
								}
								
								break;
						}
					}))
					->addValidator(new FormFieldValidator('uniqueness', function(TextFormField $formField) {
						$languageItemList = new LanguageItemList();
						$languageItemList->getConditionBuilder()->add('languageItem = ?', [$formField->getSaveValue()]);
						
						if ($languageItemList->countObjects() > 0) {
							$formField->addValidationError(
								new FormFieldValidationError(
									'notUnique',
									'wcf.acp.language.item.languageItem.error.notUnique'
								)
							);
						}
					})),
				
				MultilineTextFormField::create('languageItemValue')
					->label('wcf.acp.language.item.value')
					->required()
					->i18n()
					->i18nRequired()
			]);
		
		$this->form->appendChild($dataContainer);
		
		// `languageCategoryIDMode` is an internal field not meant to be
		// treated as real data
		$this->form->getDataHandler()->add(new VoidFormFieldDataProcessor('languageCategoryIDMode'));
		
		$this->form->getDataHandler()->add(
			new CustomFormFieldDataProcessor('languageItemOriginIsSystem', function(IFormDocument $document, array $parameters) {
				$parameters['data']['languageItemOriginIsSystem'] = 0;
				$parameters['data']['isCustomLanguageItem'] = 1;
				
				/** @var RadioButtonFormField $languageCategoryIDMode */
				$languageCategoryIDMode = $document->getNodeById('languageCategoryIDMode');
				
				// automatically determine language item
				if ($languageCategoryIDMode->getSaveValue() === 'automatic') {
					/** @var TextFormField $languageItemField */
					$languageItemField = $document->getNodeById('languageItem');
					$languageItemPieces = explode('.', $languageItemField->getSaveValue());
					
					$category = LanguageFactory::getInstance()->getCategory(
						$languageItemPieces[0] . '.' . $languageItemPieces[1] . '.' . $languageItemPieces[2]
					);
					if ($category === null) {
						$category = LanguageFactory::getInstance()->getCategory(
							$languageItemPieces[0] . '.' . $languageItemPieces[1]
						);
					}
					
					if ($category === null) {
						throw new \UnexpectedValueException("Cannot automatically determine language category for item '{$languageItemField->getSaveValue()}'.");
					}
					
					$parameters['data']['languageCategoryID'] = $category->languageCategoryID;
				}
				
				return $parameters;
			})
		);
		
		/** @var RadioButtonFormField $modeField */
		$modeField = $dataContainer->getNodeById('languageCategoryIDMode');
		
		$dataContainer->getNodeById('languageCategoryID')->addDependency(
			ValueFormFieldDependency::create('mode')
				->field($modeField)
				->values(['selection'])
		);
	}
}
