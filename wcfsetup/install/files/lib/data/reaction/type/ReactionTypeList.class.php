<?php
namespace wcf\data\reaction\type;
use wcf\data\DatabaseObjectList;

/**
 * Represents a reaction type list. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Reaction\Type
 * @since	5.2
 *
 * @method	ReactionType		current()
 * @method	ReactionType[]	        getObjects()
 * @method	ReactionType|null	search($objectID)
 * @property	ReactionType[]	        $objects
 */
class ReactionTypeList extends DatabaseObjectList {}
