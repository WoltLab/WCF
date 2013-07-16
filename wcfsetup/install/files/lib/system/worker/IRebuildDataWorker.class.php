<?php
namespace wcf\system\worker;

/**
 * Every rebuild data worker has to implement this interface.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.worker
 * @category	Community Framework
 */
interface IRebuildDataWorker extends IWorker {
	/**
	 * Returns the list of objects.
	 *
	 * @return wcf\data\DatabaseObjectList
	 */
	public function getObjectList();
}
