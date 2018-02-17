<?php
namespace wcf\data\smiley;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of smilies.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Smiley
 *
 * @method	Smiley		current()
 * @method	Smiley[]	getObjects()
 * @method	Smiley|null	search($objectID)
 * @property	Smiley[]	$objects
 */
class SmileyList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Smiley::class;
}
