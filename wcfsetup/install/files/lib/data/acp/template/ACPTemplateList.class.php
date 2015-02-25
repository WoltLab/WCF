<?php
namespace wcf\data\acp\template;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP templates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.template
 * @category	Community Framework
 */
class ACPTemplateList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\acp\template\ACPTemplate';
}
