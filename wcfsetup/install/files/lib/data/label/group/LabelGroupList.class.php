<?php
namespace wcf\data\label\group;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of label groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Label\Group
 *
 * @method	LabelGroup		current()
 * @method	LabelGroup[]		getObjects()
 * @method	LabelGroup|null		search($objectID)
 * @property	LabelGroup[]		$objects
 */
class LabelGroupList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = LabelGroup::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'label_group.showOrder ASC, label_group.groupID';
}
