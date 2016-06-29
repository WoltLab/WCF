<?php
namespace wcf\data\box\content;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of box content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Box\Content
 * @since	3.0
 *
 * @method	BoxContent		current()
 * @method	BoxContent[]	        getObjects()
 * @method	BoxContent|null	        search($objectID)
 * @property	BoxContent[]	        $objects
 */
class BoxContentList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = BoxContent::class;
}
