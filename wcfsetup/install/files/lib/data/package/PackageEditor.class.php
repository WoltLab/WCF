<?php
namespace wcf\data\package;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\PackageCacheBuilder;

/**
 * Provides functions to edit packages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package
 * @category	Community Framework
 * 
 * @method	Package		getDecoratedObject()
 * @mixin	Package
 */
class PackageEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Package::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		PackageCacheBuilder::getInstance()->reset();
	}
}
