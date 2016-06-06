<?php
namespace wcf\data\article;
use wcf\data\article\content\ViewableArticleContent;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * Represents a viewable article.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.article
 * @category	Community Framework
 * @since	2.2
 *
 * @method	Article	getDecoratedObject()
 * @mixin	Article
 */
class ViewableArticle extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Article::class;
	
	/**
	 * user profile object
	 * @var	UserProfile
	 */
	protected $userProfile = null;
	
	/**
	 * Gets a specific article decorated as viewable article.
	 *
	 * @param	integer		$articleID
	 * @return	ViewableArticle
	 */
	public static function getArticle($articleID) {
		$list = new ViewableArticleList();
		$list->setObjectIDs([$articleID]);
		$list->readObjects();
		$objects = $list->getObjects();
		if (isset($objects[$articleID])) return $objects[$articleID];
		return null;
	}
	
	/**
	 * Returns the user profile object.
	 *
	 * @return	UserProfile
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			if ($this->userID) {
				$this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
			}
			else {
				$this->userProfile = new UserProfile(new User(null, [
					'username' => $this->username
				]));
			}
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Sest the article's content.
	 *
	 * @param       ViewableArticleContent  $articleContent
	 */
	public function setArticleContent(ViewableArticleContent $articleContent) {
		if ($this->getDecoratedObject()->articleContent === null) {
			$this->getDecoratedObject()->articleContent = [];
		}
		
		$this->getDecoratedObject()->articleContent[($articleContent->languageID ?: 0)] = $articleContent;
	}
}
