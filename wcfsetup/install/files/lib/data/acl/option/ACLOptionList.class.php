<?php
namespace wcf\data\acl\option;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of acl options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acl.option
 * @category	Community Framework
 * 
 * @method	ACLOption		current()
 * @method	ACLOption[]		getObjects()
 * @method	ACLOption|null		search($objectID)
 * @property	ACLOption[]		$objects
 */
class ACLOptionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ACLOption::class;
}
