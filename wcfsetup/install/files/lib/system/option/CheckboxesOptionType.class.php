<?php
namespace wcf\system\option;

/**
 * Option type implementation for checkboxes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class CheckboxesOptionType extends MultiSelectOptionType {
	/**
	 * @see	\wcf\wcf\system\option\MultiSelectOptionType::$formElementTemplate
	 */
	protected $formElementTemplate = 'checkboxesOptionType';
	
	/**
	 * @see	\wcf\wcf\system\option\MultiSelectOptionType::$formElementTemplate
	 */
	protected $searchableFormElementTemplate = 'checkboxesSearchableOptionType';
}
