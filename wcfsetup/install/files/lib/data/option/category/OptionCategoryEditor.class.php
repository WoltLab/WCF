<?php
namespace wcf\data\option\category;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.option.category
 * @category	Community Framework
 */
class OptionCategoryEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\option\category\OptionCategory';
}
