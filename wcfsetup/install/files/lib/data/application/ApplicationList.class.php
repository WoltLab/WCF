<?php
namespace wcf\data\application;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of applications.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application
 * @category	Community Framework
 */
class ApplicationList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\application\Application';
}
