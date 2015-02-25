<?php
namespace wcf\data\spider;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of spiders.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.spider
 * @category	Community Framework
 */
class SpiderList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\spider\Spider';
}
