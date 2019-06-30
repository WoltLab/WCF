<?php
namespace wcf\system\form\builder\field\wysiwyg;
use wcf\data\IMessageQuoteAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\data\processor\CustomFormFieldDataProcessor;
use wcf\system\form\builder\field\IMaximumLengthFormField;
use wcf\system\form\builder\field\IMinimumLengthFormField;
use wcf\system\form\builder\field\TMaximumLengthFormField;
use wcf\system\form\builder\field\TMinimumLengthFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IObjectTypeFormNode;
use wcf\system\form\builder\TObjectTypeFormNode;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\quote\MessageQuoteManager;
use wcf\util\StringUtil;

/**
 * Implementation of a form field for wysiwyg editors.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class WysiwygFormField extends AbstractFormField implements IMaximumLengthFormField, IMinimumLengthFormField, IObjectTypeFormNode {
	use TMaximumLengthFormField;
	use TMinimumLengthFormField;
	use TObjectTypeFormNode;
	
	/**
	 * identifier used to autosave the field value; if empty, autosave is disabled
	 * @var	string
	 */
	protected $autosaveId = '';
	
	/**
	 * input processor containing the wysiwyg text
	 * @var	HtmlInputProcessor
	 */
	protected $htmlInputProcessor;
	
	/**
	 * last time the field has been edited; if `0`, the last edit time is unknown
	 * @var	int
	 */
	protected $lastEditTime = 0;
	
	/**
	 * quote-related data used to create the JavaScript quote manager
	 * @var	null|array
	 */
	protected $quoteData;
	
	/**
	 * is `true` if this form field supports attachments, otherwise `false`
	 * @var	boolean 
	 */
	protected $supportAttachments = false;
	
	/**
	 * is `true` if this form field supports mentions, otherwise `false`
	 * @var	boolean
	 */
	protected $supportMentions = false;
	
	/**
	 * is `true` if this form field supports quotes, otherwise `false`
	 * @var	boolean
	 */
	protected $supportQuotes = false;
	
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
	public function autosaveId($autosaveId) {
		$this->autosaveId = $autosaveId;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function cleanup() {
		MessageQuoteManager::getInstance()->saved();
	}
	
	/**
	 * Returns the identifier used to autosave the field value. If autosave is disabled,
	 * an empty string is returned.
	 * 
	 * @return	string
	 */
	public function getAutosaveId() {
		return $this->autosaveId;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		if ($this->supportsQuotes()) {
			MessageQuoteManager::getInstance()->assignVariables();
		}
		
		return parent::getHtml();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeDefinition() {
		return 'com.woltlab.wcf.message';
	}
	
	/**
	 * Returns the last time the field has been edited. If no last edit time has
	 * been set, `0` is returned.
	 * 
	 * @return	int
	 */
	public function getLastEditTime() {
		return $this->lastEditTime;
	}
	
	/**
	 * Returns all quote data or specific quote data if an argument is given.
	 * 
	 * @param	null|string	$index		quote data index
	 * @return	string[]|string
	 * 
	 * @throws	\BadMethodCallException		if quotes are not supported for this field
	 * @throws	\InvalidArgumentException	if unknown quote data is requested
	 */
	public function getQuoteData($index = null) {
		if (!$this->supportQuotes()) {
			throw new \BadMethodCallException("Quotes are not supported.");
		}
		
		if ($index === null) {
			return $this->quoteData;
		}
		
		if (!isset($this->quoteData[$index])) {
			throw new \InvalidArgumentException("Unknown quote data '{$index}'.");
		}
		
		return $this->quoteData[$index];
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasSaveValue() {
		return false;
	}
	
	/**
	 * Sets the last time this field has been edited and returns this field.
	 * 
	 * @param	int	$lastEditTime	last time field has been edited
	 * @return	WysiwygFormField	this field
	 */
	public function lastEditTime($lastEditTime) {
		$this->lastEditTime = $lastEditTime;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate() {
		parent::populate();
		
		$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('wysiwyg', function(IFormDocument $document, array $parameters) {
			if ($this->checkDependencies()) {
				$parameters[$this->getObjectProperty() . '_htmlInputProcessor'] = $this->htmlInputProcessor;
			}
			
			return $parameters;
		}));
		
		return $this;
	}

	/**
	 * Sets the data required for advanced quote support for when quotable content is present
	 * on the active page and returns this field.
	 * 
	 * Calling this method automatically enables quote support for this field.
	 * 
	 * @param	string		$objectType	name of the relevant `com.woltlab.wcf.message.quote` object type
	 * @param	string		$actionClass	action class implementing `wcf\data\IMessageQuoteAction`
	 * @param	string[]	$selectors	selectors for the quotable content (required keys: `container`, `messageBody`, and `messageContent`)
	 * @return	static
	 * 
	 * @throws	\InvalidArgumentException	if any of the given arguments is invalid
	 */
	public function quoteData($objectType, $actionClass, array $selectors = []) {
		if (ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.message.quote', $objectType) === null) {
			throw new \InvalidArgumentException("Unknown message quote object type '{$objectType}'.");
		}
		
		if (!class_exists($actionClass)) {
			throw new \InvalidArgumentException("Unknown class '{$actionClass}'");
		}
		if (!is_subclass_of($actionClass, IMessageQuoteAction::class)) {
			throw new \InvalidArgumentException("'{$actionClass}' does not implement '" . IMessageQuoteAction::class . "'.");
		}
		
		if (!empty($selectors)) {
			foreach (['container', 'messageBody', 'messageContent'] as $selector) {
				if (!isset($selectors[$selector])) {
					throw new \InvalidArgumentException("Missing selector '{$selector}'.");
				}
			}
		}
		
		$this->supportQuotes();
		
		$this->quoteData = [
			'actionClass' => $actionClass,
			'objectType' => $objectType,
			'selectors' => $selectors
		];
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if (is_string($value)) {
				$this->value = StringUtil::trim($value);
			}
		}
		
		if ($this->supportsQuotes()) {
			MessageQuoteManager::getInstance()->readFormParameters();
		}
		
		return $this;
	}
	
	/**
	 * Sets if the form field supports attachments and returns this field.
	 * 
	 * @param	boolean		$supportAttachments
	 * @return	WysiwygFormField		this field
	 */
	public function supportAttachments($supportAttachments = true) {
		$this->supportAttachments = $supportAttachments;
		
		return $this;
	}
	
	/**
	 * Sets if the form field supports mentions and returns this field.
	 * 
	 * @param	boolean		$supportMentions
	 * @return	WysiwygFormField		this field
	 */
	public function supportMentions($supportMentions = true) {
		$this->supportMentions = $supportMentions;
		
		return $this;
	}
	
	/**
	 * Sets if the form field supports quotes and returns this field.
	 * 
	 * @param	boolean		$supportQuotes
	 * @return	WysiwygFormField		this field
	 */
	public function supportQuotes($supportQuotes = true) {
		$this->supportQuotes = $supportQuotes;
		
		if (!$this->supportsQuotes()) {
			// unset previously set quote data
			$this->quoteData = null;
		}
		else {
			MessageQuoteManager::getInstance()->readParameters();
		}
		
		return $this;
	}
	
	/**
	 * Returns `true` if the form field supports attachments and returns `false` otherwise.
	 * 
	 * Important: If this method returns `true`, it does not necessarily mean that attachment
	 * support will also work as that is the task of `WysiwygAttachmentFormField`. This method
	 * is primarily relevant to inform the JavaScript API that the field supports attachments
	 * so that the relevant editor plugin is loaded.
	 * 
	 * By default, attachments are not supported.
	 * 
	 * @return	boolean
	 */
	public function supportsAttachments() {
		return $this->supportAttachments;
	}
	
	/**
	 * Returns `true` if the form field supports mentions and returns `false` otherwise.
	 * 
	 * By default, mentions are not supported.
	 * 
	 * @return	boolean
	 */
	public function supportsMentions() {
		return $this->supportMentions;
	}
	
	/**
	 * Returns `true` if the form field supports quotes and returns `false` otherwise.
	 * 
	 * By default, quotes are not supported.
	 * 
	 * @return	boolean
	 */
	public function supportsQuotes() {
		return $this->supportQuotes;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		$this->htmlInputProcessor = new HtmlInputProcessor();
		$this->htmlInputProcessor->process($this->getValue(), $this->getObjectType()->objectType);
		
		if ($this->isRequired() && $this->htmlInputProcessor->appearsToBeEmpty()) {
			$this->addValidationError(new FormFieldValidationError('empty'));
		}
		else {
			$message = $this->htmlInputProcessor->getTextContent();
			$this->validateMinimumLength($message);
			$this->validateMaximumLength($message);
		}
		
		parent::validate();
	}
}
