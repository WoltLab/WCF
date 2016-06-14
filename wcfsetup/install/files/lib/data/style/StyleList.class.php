<?php
namespace wcf\data\style;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of styles.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Style
 *
 * @method	Style		current()
 * @method	Style[]		getObjects()
 * @method	Style|null	search($objectID)
 * @property	Style[]		$objects
 */
class StyleList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Style::class;
}
