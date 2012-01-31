<?php
namespace wcf\data\cronjob;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\WCF;
use wcf\system\cache\CacheHandler;

/**
 * Provides functions to edit cronjobs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cronjob
 * @category 	Community Framework
 */
class CronjobEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\cronjob\Cronjob';
	
	/**
	 * @see wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.cronjobs-*');
	}
}
