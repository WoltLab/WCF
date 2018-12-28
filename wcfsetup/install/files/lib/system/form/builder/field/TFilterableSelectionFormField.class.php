<?php
namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IFilterableSelectionFormField` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
trait TFilterableSelectionFormField {
	use TSelectionFormField;
	
	/**
	 * `true` if this field's options are filterable by the user
	 * @var	bool
	 */
	protected $__filterable = false;
	
	/**
	 * Sets if the selection options can be filtered by the user so that they
	 * are able to quickly find the desired option out of a larger list of
	 * available options.
	 *
	 * @param	bool	$filterable	determines if field's options are filterable by user
	 * @return	static			this node
	 */
	public function filterable($filterable = true) {
		$this->__filterable = $filterable;
		
		return $this;
	}
	
	/**
	 * Returns `true` if the selection options can be filtered by the user so
	 * that they are able to quickly find the desired option out of a larger
	 * list of available options and returns `false` otherwise.
	 *
	 * Per default, fields are not filterable.
	 *
	 * @return	bool
	 */
	public function isFilterable() {
		return $this->__filterable;
	}
}
