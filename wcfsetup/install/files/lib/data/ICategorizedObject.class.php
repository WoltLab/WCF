<?php
namespace wcf\data;

/**
 * Every categorized object has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface ICategorizedObject {
	/**
	 * Returns the category this object belongs to.
	 * 
	 * @return	\wcf\data\category\Category
	 */
	public function getCategory();
}
