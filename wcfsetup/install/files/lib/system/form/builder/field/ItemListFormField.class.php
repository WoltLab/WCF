<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\data\CustomFormFieldDataProcessor;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IFormNode;
use wcf\util\ArrayUtil;

/**
 * Implementation of a form field that allows entering a list of items.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class ItemListFormField extends AbstractFormField {
	/**
	 * type of the returned save value (see `SAVE_VALUE_TYPE_*` constants)
	 * @var	string
	 */
	protected $saveValueType;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__itemListFormField';
	
	/**
	 * save value return type so that an array with the item values will be returned
	 * @var	string
	 */
	const SAVE_VALUE_TYPE_ARRAY = 'array';
	
	/**
	 * save value return type so that comma-separated list with the item values
	 * will be returned
	 * @var	string
	 */
	const SAVE_VALUE_TYPE_CSV = 'csv';
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue() {
		return is_array($this->getValue()) ? implode(',', $this->getValue()) : '';
	}
	
	/**
	 * Returns the type of the returned save value (see `SAVE_VALUE_TYPE_*` constants).
	 * 
	 * If no save value type has been set, `SAVE_VALUE_TYPE_CSV` will be set and returned.
	 * 
	 * @return	string
	 */
	public function getSaveValueType(): string {
		if ($this->saveValueType === null) {
			$this->saveValueType = self::SAVE_VALUE_TYPE_CSV;
		}
		
		return $this->saveValueType;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasSaveValue(): bool {
		// only a string can be returned as a simple save value
		return $this->getSaveValueType() === self::SAVE_VALUE_TYPE_CSV;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate(): IFormNode {
		parent::populate();
		
		// an array should be passed as a parameter outside of the `data` array
		if ($this->getSaveValueType() === self::SAVE_VALUE_TYPE_ARRAY) {
			$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('itemList', function(IFormDocument $document, array $parameters) {
				if (is_array($this->getValue())) {
					$parameters[$this->getId()] = $this->getValue();
				}
				
				return $parameters;
			}));
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue(): IFormField {
		if (isset($_POST[$this->getPrefixedId()]) && is_array($_POST[$this->getPrefixedId()])) {
			$this->__value = array_unique(ArrayUtil::trim($_POST[$this->getPrefixedId()]));
		}
		
		return $this;
	}
	
	/**
	 * Sets the type of the returned save value (see `SAVE_VALUE_TYPE_*` constants).
	 * 
	 * @param	string			$saveValueTyp	type of the returned save value
	 * @return	ItemListFormField			this field
	 * @throws	\BadMethodCallException			if save value type has already been set 
	 */
	public function saveValueType(string $saveValueType): ItemListFormField {
		if ($this->saveValueType !== null) {
			throw new \BadMethodCallException("Save value type has already been set.");
		}
		
		$this->saveValueType = $saveValueType;
		
		return $this;
	}
}
