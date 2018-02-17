<?php
namespace wcf\system\page\handler;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\data\IAccessibleObject;
use wcf\system\exception\ParentClassException;
use wcf\system\WCF;

/**
 * Implementation of the `IOnlineLocationPageHandler` interface for decorated category-bound pages.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Page\Handler
 * @since	3.0
 */
trait TDecoratedCategoryOnlineLocationPageHandler {
	use TOnlineLocationPageHandler;
	
	/**
	 * Returns the name of the decorated class name.
	 *
	 * @return	string
	 */
	abstract protected function getDecoratedCategoryClass();
	
	/**
	 * Returns the textual description if a user is currently online viewing this page.
	 *
	 * @param	Page		$page		visited page
	 * @param	UserOnline	$user		user online object with request data
	 * @return	string
	 * @see	IOnlineLocationPageHandler::getOnlineLocation()
	 */
	public function getOnlineLocation(Page $page, UserOnline $user) {
		if ($user->pageObjectID === null) {
			return '';
		}
		
		$className = $this->getDecoratedCategoryClass();
		if (!is_subclass_of($className, AbstractDecoratedCategory::class)) {
			throw new ParentClassException($className, AbstractDecoratedCategory::class);
		}
		
		/** @var AbstractDecoratedCategory $category */
		/** @noinspection PhpUndefinedMethodInspection */
		$category = $className::getCategory($user->pageObjectID);
		if ($category === null) {
			return '';
		}
		
		if ($category instanceof IAccessibleObject && !$category->isAccessible()) {
			return null;
		}
		
		return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.'.$page->identifier, ['category' => $category]);
	}
}
