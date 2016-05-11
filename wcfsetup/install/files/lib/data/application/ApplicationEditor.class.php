<?php
namespace wcf\data\application;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\ApplicationCacheBuilder;

/**
 * Provides functions to edit applications.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application
 * @category	Community Framework
 * 
 * @method	Application	getDecoratedObject()
 * @mixin	Application
 */
class ApplicationEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Application::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		ApplicationCacheBuilder::getInstance()->reset();
	}
}
