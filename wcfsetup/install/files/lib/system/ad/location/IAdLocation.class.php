<?php
namespace wcf\system\ad\location;

/**
 * Every ad location that provides custom variables has to provide a PHP class implementing this
 * interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Ad\Location
 * @since	5.2
 */
interface IAdLocation {
	/**
	 * Returns the description of the additional variables that can be used in ads in the active
	 * user's language.
	 * 
	 * The returned description will be inserted into a list, thus each variable should be in a
	 * list item (`<li>`) element.
	 * 
	 * @return	string
	 */
	public function getVariablesDescription();
	
	/**
	 * Replaces all relevant variables in the given ad and returns the processed ad.
	 * 
	 * @return	string
	 */
	public function replaceVariables($ad);
}
