<?php
namespace wcf\data;

/**
 * Every database object action class, which belongs to database objects supporting
 * clipboard actions, has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IClipboardAction {
	/**
	 * Unmarks all marked objects.
	 */
	public function unmarkAll();
	
	/**
	 * Validates the 'unmarkAll' action.
	 */
	public function validateUnmarkAll();
}
