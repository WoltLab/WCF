<?php
namespace wcf\system\user\object\watch;
use wcf\data\article\category\ArticleCategory;
use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;

/**
 * Implementation of IUserObjectWatch for watched article categories.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Object\Watch
 */
class ArticleCategoryUserObjectWatch extends AbstractObjectTypeProcessor implements IUserObjectWatch {
	/**
	 * @inheritDoc
	 */
	public function validateObjectID($objectID) {
		$category = ArticleCategory::getCategory($objectID);
		if ($category === null) {
			throw new IllegalLinkException();
		}
		if (!$category->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function resetUserStorage(array $userIDs) {
		UserStorageHandler::getInstance()->reset($userIDs, 'unreadWatchedArticles');
		UserStorageHandler::getInstance()->reset($userIDs, 'articleSubscribedCategories');
	}
}
