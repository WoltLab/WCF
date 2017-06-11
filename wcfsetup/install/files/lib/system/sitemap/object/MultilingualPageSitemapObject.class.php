<?php
namespace wcf\system\sitemap\object;
use wcf\data\page\content\PageContent;
use wcf\data\page\content\PageContentList;
use wcf\data\page\content\SitemapPageContent;
use wcf\data\page\Page;
use wcf\data\DatabaseObject;
use wcf\page\AbstractPage;
use wcf\system\acl\simple\SimpleAclResolver;

/**
 * Multilingual page sitemap implementation.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Sitemap\Object
 * @since	3.1
 */
class MultilingualPageSitemapObject extends AbstractSitemapObjectObjectType {
	/**
	 * @inheritDoc
	 */
	public function getObjectClass() {
		return PageContent::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		/** @var $pageList PageContentList */
		$pageList = parent::getObjectList();
		
		$pageList->sqlConditionJoins = 'LEFT JOIN wcf'. WCF_N .'_page page ON (page_content.pageID = page.pageID)';
		$pageList->sqlJoins = 'LEFT JOIN wcf'. WCF_N .'_page page ON (page_content.pageID = page.pageID)';
		$pageList->getConditionBuilder()->add('page.isMultilingual = ?', [1]);
		$pageList->getConditionBuilder()->add('page.allowSpidersToIndex = ?', [1]);
		
		return $pageList;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		/** @var $object PageContent */
		$page = new Page($object->pageID);
		
		if ($page->isDisabled) {
			return false;
		}
		
		if ($page->requireObjectID) {
			return false;
		}
		
		if (!$page->validateOptions()) {
			return false;
		}
		
		if ($page->permissions) {
			$permissions = explode(',', $object->permissions);
			foreach ($permissions as $permission) {
				if (!self::getGuestUserProfile()->getPermission($permission)) {
					return false;
				}
			}
		}
		
		if (!SimpleAclResolver::getInstance()->canAccess('com.woltlab.wcf.page', $object->pageID, self::getGuestUserProfile()->getDecoratedObject())) {
			return false;
		}
		
		if (!empty($page->controller)) {
			/** @var $pageClass AbstractPage */
			$pageClass = new $page->controller;
			
			if ($pageClass->loginRequired) {
				return false;
			}
			
			if ($pageClass->neededPermissions) {
				foreach ($pageClass->neededPermissions as $permission) {
					if (!self::getGuestUserProfile()->getPermission($permission)) {
						return false;
					}
				}
			}
			
			if ($pageClass->neededModules) {
				foreach ($pageClass->neededModules as $module) {
					if (!defined($module) || !constant($module)) {
						return false;
					}
				}
			}
		}
		
		return true;
	}
}
