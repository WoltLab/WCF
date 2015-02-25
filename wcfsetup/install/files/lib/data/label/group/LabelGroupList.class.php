<?php
namespace wcf\data\label\group;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of label groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.label.group
 * @category	Community Framework
 */
class LabelGroupList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\label\group\LabelGroup';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'label_group.showOrder ASC, label_group.groupID';
}
