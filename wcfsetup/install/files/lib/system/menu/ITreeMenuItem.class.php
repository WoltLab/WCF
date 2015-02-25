<?php
namespace wcf\system\menu;

/**
 * Any tree menu item should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu
 * @category	Community Framework
 */
interface ITreeMenuItem {
	/**
	 * Returns the link of this item.
	 * 
	 * @return	string
	 */
	public function getLink();
}
