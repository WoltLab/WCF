<?php
namespace wcf\system\form\builder;
use wcf\data\IStorableObject;
use wcf\system\form\builder\button\FormButton;
use wcf\system\form\builder\button\IFormButton;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\data\FormDataHandler;
use wcf\system\form\builder\data\IFormDataHandler;
use wcf\system\form\builder\field\data\processor\DefaultFormFieldDataProcessor;
use wcf\system\form\builder\field\IFileFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\WCF;

/**
 * Represents a "whole" form (document).
 * 
 * The default button of this class is a button with id `submitButton`, label `wcf.global.button.submit`,
 * access key `s` and CSS class `buttonPrimary`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder
 * @since	5.2
 */
class FormDocument implements IFormDocument {
	use TFormNode;
	use TFormParentNode {
		TFormParentNode::cleanup insteadof TFormNode;
		hasValidationErrors as protected traitHasValidationErrors;
		readValues as protected traitReadValues;
		validate as protected traitValidate;
	}
	
	/**
	 * `action` property of the HTML `form` element
	 * @var	string
	 */
	protected $action;
	
	/**
	 * `true` if the default button is added and `false` otherwise
	 * @var	boolean
	 */
	protected $addDefaultButton = true;
	
	/**
	 * `true` if form is requested via an AJAX request or processes data via an AJAX request
	 * and `false` otherwise
	 * @var	boolean
	 */
	protected $ajax = false;
	
	/**
	 * buttons registered for this form document
	 * @var	IFormButton[]
	 */
	protected $buttons = [];
	
	/**
	 * data handler for this form document
	 * @var	IFormDataHandler
	 */
	protected $dataHandler;
	
	/**
	 * encoding type of this form
	 * @var	null|
	 */
	protected $enctype = '';
	
	/**
	 * global form error message
	 * @var	null|string
	 */
	protected $errorMessage;
	
	/**
	 * is `true` if form document has already been built and is `false` otherwise
	 * @var	bool
	 */
	protected $isBuilt = false;
	
	/**
	 * is `true` if form document is in invalid due to external factors and is `false` otherwise
	 * @var	boolean
	 */
	protected $invalid = false;
	
	/**
	 * form mode (see `self::FORM_MODE_*` constants)
	 * @var	null|string
	 */
	protected $formMode;
	
	/**
	 * `method` property of the HTML `form` element
	 * @var	string
	 */
	protected $method = 'post';
	
	/**
	 * global form prefix that is prepended to form elements' names and ids to
	 * avoid conflicts with other forms
	 * @var	string
	 */
	protected $prefix;
	
	/**
	 * request data of the form's field
	 * @var	null|array
	 */
	protected $requestData;
	
	/**
	 * is `true` if global form error message will be shown if there are validation errors and
	 * is `false` otherwise
	 * @var	boolean
	 */
	protected $showErrorMessage = true;
	
	/**
	 * is `true` if global form success message will be shown and is `false` otherwise
	 * @var	boolean
	 */
	protected $showSuccessMessage = false;
	
	/**
	 * global form success message
	 * @var	null|string
	 */
	protected $successMessage;
	
	/**
	 * Cleans up the form document before the form document object is destroyed.
	 */
	public function __destruct() {
		$this->cleanup();
	}
	
	/**
	 * @inheritDoc
	 */
	public function action($action) {
		$this->action = $action;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function addButton(IFormButton $button) {
		$this->buttons[] = $button;
		
		$button->parent($this);
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function addDefaultButton($addDefaultButton = true) {
		if ($this->isBuilt) {
			throw new \BadMethodCallException("After the form document has already been built, changing whether the default button is added is no possible anymore.");
		}
		
		$this->addDefaultButton = $addDefaultButton;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function ajax($ajax = true) {
		$this->ajax = $ajax;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function build() {
		if ($this->isBuilt) {
			throw new \BadMethodCallException("Form document has already been built.");
		}
		
		// add default button
		if ($this->hasDefaultButton()) {
			$this->createDefaultButton();
		}
		
		$nodeIds = [];
		$doubleNodeIds = [];
		
		/** @var IFormNode $node */
		foreach ($this->getIterator() as $node) {
			if (in_array($node->getId(), $nodeIds)) {
				$doubleNodeIds[] = $node->getId();
			}
			else {
				$nodeIds[] = $node->getId();
			}
			
			$node->populate();
			
			if ($node instanceof IFormParentNode) {
				foreach ($node->children() as $child) {
					$node->validateChild($child);
				}
			}
		}
		
		foreach ($this->getButtons() as $button) {
			if (in_array($button->getId(), $nodeIds)) {
				$doubleNodeIds[] = $button->getId();
			}
			else {
				$nodeIds[] = $button->getId();
			}
		}
		
		if (!empty($doubleNodeIds)) {
			throw new \LogicException("Non-unique node id" . (count($doubleNodeIds) > 1 ? 's' : '') . " '" . implode("', '", $doubleNodeIds) . "'.");
		}
		
		$this->isBuilt = true;
		
		return $this;
	}
	
	/**
	 * Creates the default button for this form document.
	 */
	protected function createDefaultButton() {
		$this->addButton(
			FormButton::create('submitButton')
				->label('wcf.global.button.submit')
				->accessKey('s')
				->submit(!$this->isAjax())
				->addClass('buttonPrimary')
		);
	}
	
	/**
	 * @inheritDoc
	 */
	public function errorMessage($languageItem = null, array $variables = []) {
		if ($languageItem === null) {
			if (!empty($variables)) {
				throw new \InvalidArgumentException("Cannot use variables when unsetting error message of form '{$this->getId()}'");
			}
			
			$this->errorMessage = null;
		}
		else {
			if (!is_string($languageItem)) {
				throw new \InvalidArgumentException("Given error message language item is no string, " . gettype($languageItem) . " given.");
			}
			
			$this->errorMessage = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function formMode($formMode) {
		if ($this->formMode !== null) {
			throw new \BadMethodCallException("Form mode has already been set");
		}
		
		if ($formMode !== self::FORM_MODE_CREATE && $formMode !== self::FORM_MODE_UPDATE) {
			throw new \InvalidArgumentException("Unknown form mode '{$formMode}' given.");
		}
		
		$this->formMode = $formMode;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAction() {
		if ($this->action === null && !$this->isAjax()) {
			throw new \BadMethodCallException("Action has not been set.");
		}
		
		return $this->action;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getButtons() {
		return $this->buttons;
	}

	/**
	 * @inheritDoc
	 */
	public function getData() {
		return $this->getDataHandler()->getData($this);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDataHandler() {
		if ($this->dataHandler === null) {
			$this->dataHandler = new FormDataHandler();
			$this->dataHandler->add(new DefaultFormFieldDataProcessor());
		}
		
		return $this->dataHandler;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDocument() {
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEnctype() {
		if ($this->enctype === '') {
			/** @var IFormNode $node */
			foreach ($this->getIterator() as $node) {
				if ($node instanceof IFileFormField) {
					$this->enctype = 'multipart/form-data';
					
					return $this->enctype;
				}
			}
			
			$this->enctype = null;
		}
		
		return $this->enctype;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getErrorMessage() {
		if ($this->errorMessage === null) {
			$this->errorMessage = WCF::getLanguage()->getDynamicVariable('wcf.global.form.error');
		}
		
		return $this->errorMessage;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormMode() {
		if ($this->formMode === null) {
			$this->formMode = self::FORM_MODE_CREATE;
		}
		
		return $this->formMode;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		return WCF::getTPL()->fetch(
			'__form',
			'wcf',
			array_merge($this->getHtmlVariables(), ['form' => $this])
		);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMethod() {
		return $this->method;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPrefix() {
		if ($this->prefix === null) {
			return '';
		}
		
		return $this->prefix . '_';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getRequestData($index = null) {
		if ($this->requestData === null) {
			$this->requestData = $_POST;
		}
		
		if ($index !== null) {
			if (!isset($this->requestData[$index])) {
				throw new \InvalidArgumentException("Unknown request data with index '" . $index . "'.");
			}
			
			return $this->requestData[$index];
		}
		
		return $this->requestData;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSuccessMessage() {
		if ($this->successMessage === null) {
			$suffix = 'edit';
			if ($this->getFormMode() === IFormDocument::FORM_MODE_CREATE) {
				$suffix = 'add';
			}
			
			$this->successMessage = WCF::getLanguage()->getDynamicVariable('wcf.global.success.' . $suffix);
		}
		
		return $this->successMessage;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasDefaultButton() {
		return $this->addDefaultButton;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasValidationErrors() {
		return $this->isInvalid() || $this->traitHasValidationErrors();
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasRequestData($index = null) {
		$requestData = $this->getRequestData();
		
		if ($index !== null) {
			return isset($requestData[$index]);
		}
		
		return !empty($requestData);
	}
	
	/**
	 * @inheritDoc
	 */
	public function invalid($invalid = true) {
		$this->invalid = $invalid;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAjax() {
		return $this->ajax;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isInvalid() {
		return $this->invalid;
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadValuesFromObject(IStorableObject $object) {
		if ($this->formMode === null) {
			$this->formMode(self::FORM_MODE_UPDATE);
		}
		
		/** @var IFormNode $node */
		foreach ($this->getIterator() as $node) {
			if ($node->isAvailable()) {
				if ($node instanceof IFormField) {
					if ($node->getObjectProperty() !== $node->getId()) {
						try {
							$node->loadValueFromObject($object);
						}
						catch (\InvalidArgumentException $e) {
							// if an object property is explicitly set,
							// ignore invalid values as this might not be
							// the appropriate field
						}
					}
					else {
						$node->loadValueFromObject($object);
					}
				}
				else if ($node instanceof IFormContainer) {
					$node->loadValuesFromObject($object);
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function method($method) {
		if ($method !== 'get' && $method !== 'post') {
			throw new \InvalidArgumentException("Invalid method '{$method}' given.");
		}
		
		$this->method = $method;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function prefix($prefix) {
		static::validateId($prefix);
		
		$this->prefix = $prefix;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValues() {
		if ($this->requestData === null) {
			$this->requestData = $_POST;
		}
		
		return $this->traitReadValues();
	}
	
	/**
	 * @inheritDoc
	 */
	public function requestData(array $requestData) {
		if ($this->requestData !== null) {
			throw new \BadMethodCallException('Request data has already been set.');
		}
		
		$this->requestData = $requestData;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function showErrorMessage($showErrorMessage = true) {
		$this->showErrorMessage = $showErrorMessage;

		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function showSuccessMessage($showSuccessMessage = true) {
		$this->showSuccessMessage = $showSuccessMessage;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function showsErrorMessage() {
		return $this->showErrorMessage;
	}
	
	/**
	 * @inheritDoc
	 */
	public function showsSuccessMessage() {
		return $this->showSuccessMessage;
	}
	
	/**
	 * @inheritDoc
	 */
	public function successMessage($languageItem = null, array $variables = []) {
		if ($languageItem === null) {
			if (!empty($variables)) {
				throw new \InvalidArgumentException("Cannot use variables when unsetting success message of form '{$this->getId()}'");
			}
			
			$this->successMessage = null;
		}
		else {
			if (!is_string($languageItem)) {
				throw new \InvalidArgumentException("Given success message language item is no string, " . gettype($languageItem) . " given.");
			}
			
			$this->successMessage = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		// check security token
		if (!isset($_POST['t']) || !WCF::getSession()->checkSecurityToken($_POST['t'])) {
			$this->invalid();
			
			$this->errorMessage('wcf.global.form.error.securityToken');
		}
		else {
			$this->traitValidate();
		}
	}
}
