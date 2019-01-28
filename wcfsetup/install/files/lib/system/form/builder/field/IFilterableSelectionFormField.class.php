<?php
namespace wcf\system\form\builder\field;
use wcf\data\DatabaseObjectList;

/**
 * Represents a form field that consists of a predefined set of possible values
 * which also support filtering when the user selects their value(s).
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
interface IFilterableSelectionFormField extends ISelectionFormField {
	/**
	 * Sets if the selection options can be filtered by the user so that they
	 * are able to quickly find the desired option out of a larger list of
	 * available options.
	 *
	 * @param	bool	$filterable	determines if field's options are filterable by user
	 * @return	static			this node
	 */
	public function filterable($filterable = true);
	
	/**
	 * Returns `true` if the selection options can be filtered by the user so
	 * that they are able to quickly find the desired option out of a larger
	 * list of available options and returns `false` otherwise.
	 *
	 * Per default, fields are not filterable.
	 *
	 * @return	bool
	 */
	public function isFilterable();
}
