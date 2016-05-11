<?php
namespace wcf\data\page;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\PageCacheBuilder;
use wcf\system\cache\builder\RoutingCacheBuilder;

/**
 * Provides functions to edit pages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page
 * @category	Community Framework
 * @since	2.2
 * 
 * @method	Page	getDecoratedObject()
 * @mixin	Page
 */
class PageEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Page::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		RoutingCacheBuilder::getInstance()->reset();
		PageCacheBuilder::getInstance()->reset();
	}
}
