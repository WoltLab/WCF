<?php
namespace wcf\data\modification\log;
use wcf\data\ITitledLinkObject;

/**
 * Common interface for modification log handlers that support item processing for
 * display in the global modification log.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Log\Modification
 * @since       5.2
 */
interface IViewableModificationLog {
	/**
	 * Returns the title of the affected object. If the object does not exist
	 * anymore, this method should return an empty string instead. (nullable
	 * requires PHP 7.1)
	 * 
	 * @return ITitledLinkObject|null
	 */
	public function getAffectedObject();
}
