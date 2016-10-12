<?php
namespace wcf\data\acp\template;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP templates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Template
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
