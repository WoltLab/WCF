<?php
namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IMinimumLengthFormField` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
trait TMinimumLengthFormField {
	/**
	 * minimum length of the field value
	 * @var	null|int
	 */
	protected $__minimumLength;
	
	/**
	 * Returns the minimum length of the values of this field or `null` if no placeholder
	 * has been set.
	 * 
	 * @return	null|int
	 */
	public function getMinimumLength() {
		return $this->__minimumLength;
	}
	
	/**
	 * Sets the minimum length of the values of this field. If `null` is passed, the
	 * minimum length is removed.
	 * 
	 * @param	null|int	$minimumLength	minimum field value length
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given minimum length is no integer or otherwise invalid
	 */
	public function minimumLength($minimumLength = null) {
		if ($minimumLength !== null) {
			if (!is_int($minimumLength)) {
				throw new \InvalidArgumentException("Given minimum length is no int, '" . gettype($minimumLength) . "' given.");
			}
			
			if ($minimumLength < 0) {
				throw new \InvalidArgumentException("Minimum length must be non-negative, '" . $minimumLength . "' given.");
			}
		}
		
		$this->__minimumLength = $minimumLength;
		
		return $this;
	}
}