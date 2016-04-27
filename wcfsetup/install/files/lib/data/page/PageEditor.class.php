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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.page
 * @category	Community Framework
 * @since	2.2
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
