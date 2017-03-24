<?php
namespace wcf\system\version;
use wcf\data\object\type\AbstractObjectTypeProvider;

/**
 * Abstract implementation of an version tracker object type provider.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Version
 */
abstract class AbstractVersionTrackerProvider extends AbstractObjectTypeProvider implements IVersionTrackerProvider {
	/**
	 * list of properties that should be tracked
	 * @var string[]
	 */
	public static $trackedProperties = [];
}
