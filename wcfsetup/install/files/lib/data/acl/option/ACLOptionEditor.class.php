<?php
namespace wcf\data\acl\option;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit acl options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acl.option
 * @category	Community Framework
 * 
 * @method	ACLOption	getDecoratedObject()
 * @mixin	ACLOption
 */
class ACLOptionEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = ACLOption::class;
}
