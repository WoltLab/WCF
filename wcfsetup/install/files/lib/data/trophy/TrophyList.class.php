<?php
namespace wcf\data\trophy;
use wcf\data\DatabaseObjectList;

/**
 * Represents a trophy list. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Trophy
 * @since	3.1
 *
 * @method	Trophy		current()
 * @method	Trophy[]	getObjects()
 * @method	Trophy|null	search($objectID)
 * @property	Trophy[]	$objects
 */
class TrophyList extends DatabaseObjectList { }
