<?php
namespace wcf\system\dialog;
use wbb\data\board\BoardCache;
use wbb\system\label\object\ThreadLabelObjectHandler;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\wysiwyg\WysiwygFormContainer;
use wcf\system\form\builder\DialogFormDocument;
use wcf\system\form\builder\field\acl\simple\SimpleAclFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\CaptchaFormField;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IconFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\label\LabelFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\RatingFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\tag\TagFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\UploadFormField;
use wcf\system\form\builder\field\user\UserFormField;
use wcf\system\form\builder\field\user\UsernameFormField;
use wcf\system\label\LabelHandler;

class TestDialog extends AbstractDatabaseObjectAction {
	/**
	 * @var	DialogFormDocument
	 */
	protected $form;
	
	public function __construct(array $objects, $action, array $parameters = []) {
		$this->action = $action;
		$this->parameters = $parameters;
	}
	
	public function validateGetDialog() {
		
	}
	
	public function getDialog() {
		$form = $this->getForm();
		
		return [
			'dialog' => $form->getHtml(),
			'formId' => $form->getId()
		];
	}
	
	public function validateSaveDialog() {
		$this->form = $this->getForm()->requestData($this->parameters['data']);
		$this->form->readValues();
	}
	
	public function saveDialog() {
		$this->form->validate();
		
		if (!$this->form->hasValidationErrors()) {
			wcfDebug($_POST, $this->form->getData());
		}
		
		return [
			'dialog' => $this->form->getHtml(),
			'formId' => $this->form->getId()
		];
	}

	/**
	 * @return DialogFormDocument
	 */
	protected function getForm() {
		$form = DialogFormDocument::create('test')
			->ajax()
			->prefix('test')
			->appendChildren([
				// WysiwygFormContainer not supported because code like new WCF.Attachment.Upload executed before
				// WCF.Attachment.js loaded `$()` is probably executed immediately
				
				FormContainer::create('c')
					->appendChildren([
						UploadFormField::create('upload')
							->label('Upload'),
						TextFormField::create('i18ntext')
							->label('i18n text')
							->i18n(),
						
						SimpleAclFormField::create('simpleAcl')
							->label('Simple Acl'),
						
						// TODO: AclFormField currently not really possible because data is not accessible outside `aclPermissionJavaScript.tpl`
						
						// BBCodesAttributes will not be supported
						// devtool fields will not be supported
						
						TagFormField::create(),
						
						UserFormField::create('user')
							->label('User'),
						
						UsernameFormField::create(),
						
						BooleanFormField::create('boolean')
							->label('Boolean'),
						
						//CaptchaFormField::create()
						//	->objectType(CAPTCHA_TYPE),
						
						DateFormField::create('date')
							->label('Date'),
						
						IconFormField::create('icon')
							->label('Icon'),
						
						IntegerFormField::create('integer')
							->label('Integer'),
						
						ItemListFormField::create('itemList')
							->label('Item List'),
						
						MultilineTextFormField::create('multilineText')
							->label('Multiline Text'),

						MultipleSelectionFormField::create('multipleSelection')
							->label('Multiple Selection')
							->options([
								1 => 1,
								2 => 2,
								3 => 3
							]),

						RadioButtonFormField::create('radioButton')
							->label('Radio Button')
							->options([
								1 => 1,
								2 => 2,
								3 => 3
							])
							->value(1),
						
						RatingFormField::create()
							->label('Rating'),

						SingleSelectionFormField::create('singleSelection')
							->label('Single Selection')
							->options([
								1 => 1,
								2 => 2,
								3 => 3
							]),
						
						TextFormField::create('text')
							->label('Text')
					])
			]);
		
		$form->getNodeById('user')->addDependency(ValueFormFieldDependency::create('username')
		->field($form->getNodeById('username'))->values(['1234']));
		
		$form->build();
		
		return $form;
	}
}
