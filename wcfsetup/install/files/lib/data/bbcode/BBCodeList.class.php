<?php
namespace wcf\data\bbcode;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of bbcodes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Bbcode
 *
 * @method	BBCode		current()
 * @method	BBCode[]	getObjects()
 * @method	BBCode|null	search($objectID)
 * @property	BBCode[]	$objects
 */
class BBCodeList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = BBCode::class;
}
