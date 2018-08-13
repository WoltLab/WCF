<?php
namespace wcf\system\user\notification\event;
use wcf\data\page\content\PageContentEditor;
use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\data\page\PageCache;
use wcf\system\cache\builder\PageCacheBuilder;
use wcf\system\cache\builder\RoutingCacheBuilder;
use wcf\system\request\ControllerMap;
use wcf\system\user\notification\TestableUserNotificationEventHandler;

/**
 * Provides a method to create a page for testing user notification
 * events.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	3.2
 */
trait TTestablePageUserNotificationEvent {
	/**
	 * Creates a moderation queue entry for a reported user.
	 * 
	 * @return	Page
	 */
	public static function getTestPage() {
		/** @var Page $page */
		$page = (new PageAction([], 'create', [
			'data' => [
				'parentPageID' => null,
				'pageType' => 'text',
				'name' => 'Page Title',
				'cssClassName' => '',
				'applicationPackageID' => 1,
				'lastUpdateTime' => TIME_NOW,
				'isMultilingual' => 0,
				'identifier' => '',
				'packageID' => 1
			],
			'content' => [
				0 => [
					'title' => 'Page Title',
					'content' => 'Page Content',
					'metaDescription' => '',
					'metaKeywords' => '',
					'customURL' => 'test-page'
				]
			]
		]))->executeAction()['returnValues'];
		$pageContents = $page->getPageContents();
		$pageContent = reset($pageContents);
		
		$editor = new PageContentEditor($pageContent);
		$editor->update([
			'customURL' => 'test-page-'. $page->pageID
		]);
		
		self::resetPageCache();
		
		return $page;
	}
	
	private static function resetPageCache() {
		// reset cache builders
		TestableUserNotificationEventHandler::getInstance()->resetCacheBuilder(PageCacheBuilder::getInstance());
		TestableUserNotificationEventHandler::getInstance()->resetCacheBuilder(RoutingCacheBuilder::getInstance());
		
		// reset page cache
		$reflectionClass = new \ReflectionClass(PageCache::class);
		$reflectionProperty = $reflectionClass->getProperty('cache');
		$reflectionProperty->setAccessible(true);
		$reflectionProperty->setValue(PageCache::getInstance(), PageCacheBuilder::getInstance()->getData());
		
		// reset controller map
		$reflectionClass = new \ReflectionClass(ControllerMap::class);
		$reflectionMethod = $reflectionClass->getMethod('init');
		$reflectionMethod->setAccessible(true);
		$reflectionMethod->invoke(ControllerMap::getInstance());
	}
}
