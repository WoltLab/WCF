<?php
namespace wcf\system\sitemap\object;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\data\DatabaseObject;
use wcf\page\AbstractPage;
use wcf\system\acl\simple\SimpleAclResolver;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;

/**
 * Simple page sitemap implementation.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Sitemap\Object
 * @since	3.1
 */
class SimplePageSitemapObject extends AbstractSitemapObjectObjectType {
	/**
	 * @inheritDoc
	 */
	public function getObjectClass() {
		return Page::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		/** @var $pageList PageList */
		$pageList = parent::getObjectList();
		$pageList->getConditionBuilder()->add('isMultilingual = ?', [0]);
		$pageList->getConditionBuilder()->add('page.allowSpidersToIndex = ?', [1]);
		
		return $pageList;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		/** @var $object Page */
		if ($object->isDisabled) {
			return false;
		}
		
		if ($object->requireObjectID) {
			return false;
		}
		
		if (!$object->validateOptions()) {
			return false;
		}
		
		if (!$object->validatePermissions()) {
			return false; 
		}
		
		if (!SimpleAclResolver::getInstance()->canAccess('com.woltlab.wcf.page', $object->pageID)) {
			return false;
		}
		
		if (!empty($object->controller)) {
			/** @var $page AbstractPage */
			$page = new $object->controller;
			
			if ($page->loginRequired) {
				return false;
			}
				
			try {
				// check modules
				$page->checkModules();
				
				// check permission
				$page->checkPermissions();
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
