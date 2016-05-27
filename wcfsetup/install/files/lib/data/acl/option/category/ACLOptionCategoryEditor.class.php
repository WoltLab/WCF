<?php
namespace wcf\data\acl\option\category;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit acl option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acl.option.category
 * @category	Community Framework
 * 
 * @method	ACLOptionCategory	getDecoratedObject()
 * @mixin	ACLOptionCategory
 */
class ACLOptionCategoryEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = ACLOptionCategory::class;
}
