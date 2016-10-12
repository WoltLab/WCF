<?php
namespace wcf\data\template\group;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of template groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Template\Group
 *
 * @method	TemplateGroup		current()
 * @method	TemplateGroup[]		getObjects()
 * @method	TemplateGroup|null	search($objectID)
 * @property	TemplateGroup[]		$objects
 */
class TemplateGroupList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = TemplateGroup::class;
}
