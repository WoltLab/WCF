<?php
namespace wcf\data\cronjob;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of cronjobs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cronjob
 * @category	Community Framework
 *
 * @method	Cronjob		current()
 * @method	Cronjob[]	getObjects()
 * @method	Cronjob|null	search($objectID)
 * @property	Cronjob[]	$objects
 */
class CronjobList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Cronjob::class;
}
