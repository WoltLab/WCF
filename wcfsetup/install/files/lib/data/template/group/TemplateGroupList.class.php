<?php
namespace wcf\data\template\group;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of template groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.group
 * @category	Community Framework
 */
class TemplateGroupList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\template\group\TemplateGroup';
}
