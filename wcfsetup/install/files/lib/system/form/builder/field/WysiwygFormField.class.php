<?php
namespace wcf\system\form\builder\field;
use wcf\data\IStorableObject;
use wcf\system\form\builder\field\data\CustomFormFieldDataProcessor;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IFormNode;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\util\StringUtil;

/**
 * Implementation of a form field for wysiwyg editors.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class WysiwygFormField extends AbstractFormField implements IMaximumLengthFormField, IMinimumLengthFormField, IObjectTypeFormField {
	use TMaximumLengthFormField;
	use TMinimumLengthFormField;
	use TObjectTypeFormField;
	
	/**
	 * identifier used to autosave the field value; if empty, autosave is disabled
	 * @var	string
	 */
	protected $__autosaveId = '';
	
	/**
	 * last time the field has been edited; if `0`, the last edit time is unknown
	 * @var	int
	 */
	protected $__lastEditTime = 0;
	
	/**
	 * input processor containing the wysiwyg text
	 * @var	HtmlInputProcessor
	 */
	protected $htmlInputProcessor;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__wysiwygFormField';
	
	/**
	 * Sets the identifier used to autosave the field value and returns this field.
	 * 
	 * @param	string		$autosaveId	identifier used to autosave field value
	 * @return	WysiwygFormField		this field
	 */
	public function autosaveId(string $autosaveId): WysiwygFormField {
		$this->__autosaveId = $autosaveId;
		
		return $this;
	}
	
	/**
	 * Returns the identifier used to autosave the field value. If autosave is disabled,
	 * an empty string is returned.
	 * 
	 * @return	string
	 */
	public function getAutosaveId(): string {
		return $this->__autosaveId;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeDefinition(): string {
		return 'com.woltlab.wcf.message';
	}
	
	/**
	 * Returns the last time the field has been edited. If no last edit time has
	 * been set, `0` is returned.
	 * 
	 * @return	int
	 */
	public function getLastEditTime(): int {
		return $this->__lastEditTime;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasSaveValue(): bool {
		return false;
	}
	
	/**
	 * Sets the last time this field has been edited and returns this field.
	 * 
	 * @param	int	$lastEditTime	last time field has been edited
	 * @return	WysiwygFormField	this field
	 */
	public function lastEditTime(int $lastEditTime): WysiwygFormField {
		$this->__lastEditTime = $lastEditTime;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate(): IFormNode {
		parent::populate();
		
		$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('wysiwyg', function(IFormDocument $document, array $parameters) {
			if ($this->checkDependencies()) {
				$parameters[$this->getObjectProperty() . 'HtmlInputProcessor'] = $this->htmlInputProcessor;
			}
			
			return $parameters;
		}));
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue(): IFormField {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if (is_string($value)) {
				$this->__value = StringUtil::trim($value);
			}
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->isRequired() && $this->getValue() === '') {
			$this->addValidationError(new FormFieldValidationError('empty'));
		}
		else {
			$this->validateMinimumLength($this->getValue());
			$this->validateMaximumLength($this->getValue());
		}
		
		$this->htmlInputProcessor = new HtmlInputProcessor();
		$this->htmlInputProcessor->process($this->getValue(), $this->getObjectType()->objectType);
		
		parent::validate();
	}
}
