<?php
namespace wcf\system\user\activity\event;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for articles.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 */
class ArticleUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$objectIDs = [];
		foreach ($events as $event) {
			$objectIDs[] = $event->objectID;
		}
		
		ViewableArticleRuntimeCache::getInstance()->cacheObjectIDs($objectIDs);
		
		// set message
		foreach ($events as $event) {
			$article = ViewableArticleRuntimeCache::getInstance()->getObject($event->objectID);
			if ($article !== null) {
				if ($article->canRead()) {
					$event->setIsAccessible();
					
					$text = WCF::getLanguage()->getDynamicVariable('wcf.article.recentActivity', ['article' => $article]);
					$event->setTitle($text);
					$event->setDescription($article->getFormattedTeaser());
				}
			}
			else {
				$event->setIsOrphaned();
			}
		}
	}
}
