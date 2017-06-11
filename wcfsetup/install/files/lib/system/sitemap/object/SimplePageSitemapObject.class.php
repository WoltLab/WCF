<?php
namespace wcf\system\sitemap\object;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\data\DatabaseObject;
use wcf\page\AbstractPage;
use wcf\system\acl\simple\SimpleAclResolver;

/**
 * Simple page sitemap implementation.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
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
		/** @var $classList PageList */
		$classList = parent::getObjectList();
		$classList->getConditionBuilder()->add('isMultilingual = ?', [0]);
		
		return $classList;
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
		
		if ($object->permissions) {
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
		
		if (!empty($object->controller)) {
			/** @var $page AbstractPage */
			$page = new $object->controller;
			
			if ($page->loginRequired) {
				return false;
			}
			
			if ($page->neededPermissions) {
				foreach ($page->neededPermissions as $permission) {
					if (!self::getGuestUserProfile()->getPermission($permission)) {
						return false;
					}
				}
			}
			
			if ($page->neededModules) {
				foreach ($page->neededModules as $module) {
					if (!defined($module) || !constant($module)) {
						return false;
					}
				}
			}
		}
		
		return true;
	}
}
