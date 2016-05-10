<?php
namespace wcf\data\acl\option\category;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of acl option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acl.option.category
 * @category	Community Framework
 * 
 * @method	ACLOptionCategory		current()
 * @method	ACLOptionCategory[]		getObjects()
 * @method	ACLOptionCategory|null		search($objectID)
 * @property	ACLOptionCategory[]		$objects
 */
class ACLOptionCategoryList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ACLOptionCategory::class;
}
