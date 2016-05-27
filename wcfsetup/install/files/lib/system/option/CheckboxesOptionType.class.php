<?php
namespace wcf\system\option;

/**
 * Option type implementation for checkboxes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class CheckboxesOptionType extends MultiSelectOptionType {
	/**
	 * @inheritDoc
	 */
	protected $formElementTemplate = 'checkboxesOptionType';
	
	/**
	 * @inheritDoc
	 */
	protected $searchableFormElementTemplate = 'checkboxesSearchableOptionType';
}
