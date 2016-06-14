<?php
namespace wcf\data\label;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of labels.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Label
 *
 * @method	Label		current()
 * @method	Label[]		getObjects()
 * @method	Label|null	search($objectID)
 * @property	Label[]		$objects
 */
class LabelList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Label::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'label.showOrder ASC, label.labelID ASC';
}
