<?php
namespace wcf\data\acl\option\category;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit acl option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acl.option.category
 * @category	Community Framework
 */
class ACLOptionCategoryEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	public static $baseClass = 'wcf\data\acl\option\category\ACLOptionCategory';
}
