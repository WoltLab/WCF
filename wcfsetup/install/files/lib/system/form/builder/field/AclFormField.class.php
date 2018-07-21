<?php
namespace wcf\system\form\builder\field;
use wcf\data\IStorableObject;
use wcf\system\acl\ACLHandler;
use wcf\system\form\builder\field\data\CustomFormFieldDataProcessor;
use wcf\system\form\builder\IFormDocument;

/**
 * Implementation of a form field for setting acl option values.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class AclFormField extends AbstractFormField implements IObjectTypeFormField {
	use TObjectTypeFormField;
	
	/**
	 * name of/filter for the name(s) of the shown acl option categories 
	 * @var	null|string
	 */
	protected $__categoryName;
	
	/**
	 * id of the edited object or `null` if no object is edited
	 * @var	null|int
	 */
	protected $objectID;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__aclFormField';
	
	/**
	 * is `true` if acl-related global JavaScript code has already been included
	 * and is `false` otherwise
	 * @var	bool
	 */
	protected static $includedAclJavaScript = false;
	
	/**
	 * Sets the name of/filter for the name(s) of the shown acl option categories and
	 * returns this field.
	 * 
	 * @param	string		$categoryName	name of/filter for the acl option categories
	 * @return	static		$this		this field
	 * 
	 * @throws	\InvalidArgumentException	if given category name is invalid
	 */
	public function categoryName(string $categoryName) {
		// TODO: validation
		
		$this->__categoryName = $categoryName;
		
		return $this;
	}
	
	/**
	 * Returns the name of/filter for the name(s) of the shown acl option categories
	 * or returns `null` if no category name has been set.
	 * 
	 * @return	null|string
	 */
	public function getCategoryName() {
		return $this->__categoryName;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtmlVariables() {
		ACLHandler::getInstance()->assignVariables($this->getObjectType()->objectTypeID);
		
		$includeAclJavaScript = !static::$includedAclJavaScript;
		if (!static::$includedAclJavaScript) {
			static::$includedAclJavaScript = true;
		}
		
		return [
			'includeAclJavaScript' => $includeAclJavaScript
		];
	}
	
	/**
	 * Returns the id of the edited object or `null` if no object is edited.
	 * 
	 * @return	null|int
	 */
	public function getObjectID() {
		return $this->objectID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeDefinition() {
		return 'com.woltlab.wcf.acl';
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasSaveValue() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadValueFromObject(IStorableObject $object) {
		$this->objectID = $object->{$object::getDatabaseTableIndexName()};
		
		if ($this->objectID === null) {
			throw new \UnexpectedValueException("Cannot read object id from object of class '" . get_class($object). "'.");
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate() {
		parent::populate();
		
		$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('acl', function(IFormDocument $document, array $parameters) {
			$parameters[$this->getObjectProperty() . '_aclObjectTypeID'] = $this->getObjectType()->objectTypeID;
			
			return $parameters;
		}));
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		ACLHandler::getInstance()->readValues($this->getObjectType()->objectTypeID);
		
		return $this;
	}
}
