<?php
namespace wcf\data\spider;
use wcf\data\DatabaseObject;

/**
 * Represents a spider.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Spider
 *
 * @property-read	integer		$spiderID		unique id of the spider
 * @property-read	string		$spiderIdentifier	unique textual identifier of the spider
 * @property-read	string		$spiderName		name of the spider
 * @property-read	string		$spiderURL		link to the spider's website or empty if no such website exists
 */
class Spider extends DatabaseObject {}
