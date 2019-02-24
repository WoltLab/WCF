<?php
namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IImmutableFormField` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
trait TImmutableFormField {
	/**
	 * `true` if the value of this field is immutable and `false` otherwise
	 * @var	bool
	 */
	protected $immutable = false;
	
	/**
	 * Sets whether the value of this field is immutable and returns this field.
	 * 
	 * @param	bool		$immutable	determines if field value is immutable
	 * @return	static				this field
	 */
	public function immutable($immutable = true) {
		$this->immutable = $immutable;
		
		return $this;
	}
	
	/**
	 * Returns `true` if the value of this field is immutable and returns `false`
	 * otherwise. By default, fields are mutable.
	 * 
	 * @return	bool
	 */
	public function isImmutable() {
		return $this->immutable;
	}
}
