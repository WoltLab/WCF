<?php
namespace wcf\data\package\installation\queue;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of package installation queues.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.installation.queue
 * @category	Community Framework
 */
class PackageInstallationQueueList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\package\installation\queue\PackageInstallationQueue';
}
