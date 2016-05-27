<?php
namespace wcf\data\user\rank;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user ranks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.rank
 * @category	Community Framework
 *
 * @method	UserRank	current()
 * @method	UserRank[]	getObjects()
 * @method	UserRank|null	search($objectID)
 * @property	UserRank[]	$objects
 */
class UserRankList extends DatabaseObjectList { }
