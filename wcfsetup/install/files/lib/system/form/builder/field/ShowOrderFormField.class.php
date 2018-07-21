<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;

/**
 * Implementation of a form field for an object's `showOrder` property which determines
 * the order in which the objects are shown.
 * 
 * The show order field provides a list of siblings and the object will be positioned
 * *after* the selected sibling. To insert objects at the very beginning, the `options()`
 * method prepends an additional option for that case.
 *
 * This field uses the `wcf.form.field.showOrder` language item as the default form
 * field label uses `showOrder` as the default node id.
 * 
 * While the options of the field work with the ids of the appropriate sibling objects
 * as keys, the value of the field is the actual position of the relevant object (from
 * `1` to `count($siblings)`.
 * 
 * If an object is edited, thus `$this->getDocument()->getFormMode() === IFormDocument::FORM_MODE_UPDATE`,
 * it is expected that the edited objects itself is not part of the sibling list provided
 * for the field options.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class ShowOrderFormField extends SingleSelectionFormField {
	use TDefaultIdFormField;
	
	/**
	 * Creates a new instance of `ShowOrderFormField`.
	 */
	public function __construct() {
		$this->label('wcf.form.field.showOrder');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue() {
		if ($this->__value !== null) {
			$index = array_search($this->__value, array_keys($this->getOptions()));
			
			if ($index !== false) {
				return $index + 1;
			}
			
			return null;
		}
		
		return $this->__value;
	}
	
	/**
	 * @inheritDoc
	 * @return	static
	 * 
	 * There is an additional element prepended to the options with key `0`
	 * and using the language item `wcf.form.field.showOrder.firstPosition`
	 * as value to mark adding it at the first position.
	 */
	public function options($options, bool $nestedOptions = false) {
		parent::options($options, $nestedOptions);
		
		$this->__options = [0 => WCF::getLanguage()->get('wcf.form.field.showOrder.firstPosition')] + $this->__options;
		if ($nestedOptions) {
			array_unshift($this->__nestedOptions, [
				'depth' => 0,
				'label' => WCF::getLanguage()->get('wcf.form.field.showOrder.firstPosition'),
				'value' => 0
			]);
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function value($value) {
		$keys = array_keys($this->getOptions());
		
		// when editing an objects, the value has to be reduced by one to determine the
		// relevant sibling as the edited object is shown after its previous sibling 
		if ($this->getDocument()->getFormMode() === IFormDocument::FORM_MODE_UPDATE) {
			$value--;
		}
		
		if (count($keys) <= $value) {
			throw new \InvalidArgumentException("Unknown value '{$value}' as only " . count($keys) . " values are available.");
		}
		
		return parent::value($keys[$value]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getDefaultId() {
		return 'showOrder';
	}
}
