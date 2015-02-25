<?php
namespace wcf\data\acl\option;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit acl options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acl.option
 * @category	Community Framework
 */
class ACLOptionEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	public static $baseClass = 'wcf\data\acl\option\ACLOption';
}
