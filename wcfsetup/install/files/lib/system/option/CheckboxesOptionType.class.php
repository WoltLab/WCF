<?php
namespace wcf\system\option;

/**
 * Option type implementation for checkboxes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
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
	
	/**
	 * @inheritDoc
	 */
	public function getCSSClassName() {
		return 'checkboxList';
	}
}
