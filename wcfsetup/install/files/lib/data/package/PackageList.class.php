<?php
namespace wcf\data\package;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of packages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package
 *
 * @method	Package		current()
 * @method	Package[]	getObjects()
 * @method	Package|null	search($objectID)
 * @property	Package[]	$objects
 */
class PackageList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Package::class;
}
