<?php
namespace wcf\data\application;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of applications.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Application
 *
 * @method	Application		current()
 * @method	Application[]		getObjects()
 * @method	Application|null	search($objectID)
 * @property	Application[]		$objects
 */
class ApplicationList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Application::class;
}
