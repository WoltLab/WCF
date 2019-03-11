<?php
namespace wcf\system\sitemap\object;
use wcf\data\page\content\PageContent;
use wcf\data\page\content\PageContentList;
use wcf\data\page\Page;
use wcf\data\DatabaseObject;
use wcf\page\AbstractPage;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;

/**
 * Multilingual page sitemap implementation.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
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
		
		if (!$page->validatePermissions()) {
			return false; 
		}
		
		if (!SimpleAclResolver::getInstance()->canAccess('com.woltlab.wcf.page', $object->pageID)) {
			return false;
		}
		
		if (!empty($page->controller)) {
			/** @var $pageInstance AbstractPage */
			$pageInstance = new $page->controller;
			
			if ($pageInstance->loginRequired) {
				return false;
			}
			
			try {
				// check modules
				$pageInstance->checkModules();
				
				// check permission
				$pageInstance->checkPermissions();
			}
			catch (PermissionDeniedException $e) {
				return false;
			}
			catch (IllegalLinkException $e) {
				return false;
			}
		}
		
		return true;
	}
}
