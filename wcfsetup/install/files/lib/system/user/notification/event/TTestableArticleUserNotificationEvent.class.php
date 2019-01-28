<?php
namespace wcf\system\user\notification\event;
use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\data\category\Category;
use wcf\data\user\UserProfile;

/**
 * Provides a method to create a article for testing user notification
 * events.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification\Event
 * @since	5.2
 */
trait TTestableArticleUserNotificationEvent {
	/**
	 * Creates an test article. 
	 * 
	 * @param       Category        $category
	 * @param       UserProfile     $author
	 * @return      Article
	 */
	public static function getTestArticle(Category $category, UserProfile $author) {
		/** @var Article $article */
		$article = (new ArticleAction([], 'create', [
			'data' => [
				'time' => TIME_NOW,
				'categoryID' => $category->categoryID,
				'publicationStatus' => Article::PUBLISHED,
				'publicationDate' => TIME_NOW,
				'enableComments' => 1,
				'userID' => $author->userID,
				'username' => $author->username,
				'isMultilingual' => 0,
				'hasLabels' => 0
			],
			'content' => [
				0 => [
					'title' => 'Test Article',
					'teaser' => 'Test Article Teaser',
					'content' => 'Test Article Content',
					'imageID' => null,
					'teaserImageID' => null
				]
			]
		]))->executeAction()['returnValues'];
		
		return $article;
	}
}
