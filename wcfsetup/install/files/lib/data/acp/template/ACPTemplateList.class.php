<?php
namespace wcf\data\acp\template;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP templates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.template
 * @category	Community Framework
 *
 * @method	ACPTemplate		current()
 * @method	ACPTemplate[]		getObjects()
 * @method	ACPTemplate|null	search($objectID)
 * @property	ACPTemplate[]		$objects
 */
class ACPTemplateList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ACPTemplate::class;
}
