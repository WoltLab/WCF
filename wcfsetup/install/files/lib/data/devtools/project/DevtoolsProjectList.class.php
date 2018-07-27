<?php
namespace wcf\data\devtools\project;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of devtools projects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Devtools\Project
 * @since	3.1
 *
 * @method	DevtoolsProject		current()
 * @method	DevtoolsProject[]	getObjects()
 * @method	DevtoolsProject|null	search($objectID)
 * @property	DevtoolsProject[]	$objects
 */
class DevtoolsProjectList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = DevtoolsProject::class;
}
