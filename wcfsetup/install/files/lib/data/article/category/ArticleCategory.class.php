<?php
namespace wcf\data\article\category;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\ITitledLinkObject;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents an article category.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Category
 * @since	3.0
 * 
 * @method static	ArticleCategory|null	getCategory($categoryID)
 */
class ArticleCategory extends AbstractDecoratedCategory implements ITitledLinkObject {
	/**
	 * object type name of the article categories
	 * @var	string
	 */
	const OBJECT_TYPE_NAME = 'com.woltlab.wcf.article.category';
	
	/**
	 * acl permissions of this category grouped by the id of the user they
	 * belong to
	 * @var	array
	 */
	protected $userPermissions = [];
	
	/**
	 * Returns true if the category is accessible for the active user.
	 * 
	 * @param	User            $user
	 * @return	boolean
	 */
	public function isAccessible(User $user = null) {
		if ($this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) return false;
		
		// check permissions
		return $this->getPermission('canReadArticle', $user);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPermission($permission, User $user = null) {
		if ($user === null) {
			$user = WCF::getUser();
		}
		
		if (!isset($this->userPermissions[$user->userID])) {
			$this->userPermissions[$user->userID] = CategoryPermissionHandler::getInstance()->getPermissions($this->getDecoratedObject(), $user);
		}
		
		if (isset($this->userPermissions[$user->userID][$permission])) {
			return $this->userPermissions[$user->userID][$permission];
		}
		
		if ($this->getParentCategory()) {
			return $this->getParentCategory()->getPermission($permission, $user);
		}
		
		if ($user->userID === WCF::getSession()->getUser()->userID) {
			return WCF::getSession()->getPermission('user.article.'.$permission);
		}
		else {
			$userProfile = new UserProfile($user);
			return $userProfile->getPermission('user.article.'.$permission);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('CategoryArticleList', [
			'object' => $this->getDecoratedObject()
		]);
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * Returns a list with ids of accessible categories.
	 * 
	 * @param	string[]	$permissions
	 * @return	integer[]
	 */
	public static function getAccessibleCategoryIDs(array $permissions = ['canReadArticle']) {
		$categoryIDs = [];
		foreach (CategoryHandler::getInstance()->getCategories(self::OBJECT_TYPE_NAME) as $category) {
			$result = true;
			$category = new ArticleCategory($category);
			foreach ($permissions as $permission) {
				$result = $result && $category->getPermission($permission);
			}
			
			if ($result) {
				$categoryIDs[] = $category->categoryID;
			}
		}
		
		return $categoryIDs;
	}
}
