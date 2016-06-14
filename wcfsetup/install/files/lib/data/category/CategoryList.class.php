<?php
namespace wcf\data\category;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of categories.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Category
 * 
 * @method	Category	current()
 * @method	Category[]	getObjects()
 * @method	Category|null	search($objectID)
 * @property	Category[]	$objects
 */
class CategoryList extends DatabaseObjectList { }
