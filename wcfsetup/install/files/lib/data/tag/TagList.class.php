<?php
namespace wcf\data\tag;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of tags.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.tag
 * @category	Community Framework
 *
 * @method	Tag		current()
 * @method	Tag[]		getObjects()
 * @method	Tag|null	search($objectID)
 * @property	Tag[]		$objects
 */
class TagList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Tag::class;
}
