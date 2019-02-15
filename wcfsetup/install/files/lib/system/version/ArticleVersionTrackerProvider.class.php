<?php
namespace wcf\system\version;
use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\data\article\ArticleList;
use wcf\data\article\ArticleVersionTracker;
use wcf\data\IVersionTrackerObject;

/**
 * Version tracker object type provider implementation for articles.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Version
 * @since	3.1
 */
class ArticleVersionTrackerProvider extends AbstractVersionTrackerProvider {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.article.list';
	
	/**
	 * @inheritDoc
	 */
	public $className = Article::class;
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ArticleVersionTracker::class;
	
	/**
	 * @inheritDoc
	 */
	public $listClassName = ArticleList::class;
	
	/**
	 * @inheritDoc
	 */
	public $permissionCanAccess = 'admin.content.article.canManageArticle';
	
	/**
	 * @inheritDoc
	 */
	public static $defaultProperty = 'content';
	
	/**
	 * @inheritDoc
	 */
	public static $propertyLabels = [
		'content' => 'wcf.acp.article.content',
		'teaser' => 'wcf.acp.article.teaser',
		'title' => 'wcf.global.title'
	];
	
	/**
	 * @inheritDoc
	 */
	public static $trackedProperties = ['title', 'teaser', 'content'];
	
	/**
	 * @inheritDoc
	 */
	public function getCurrentVersion(IVersionTrackerObject $object) {
		$properties = $this->getTrackedProperties();
		
		/** @var Article $object */
		$payload = [];
		foreach ($object->getArticleContents() as $languageID => $articleContent) {
			$payload[$languageID] = [];
			foreach ($properties as $property) {
				$payload[$languageID][$property] = $articleContent->{$property};
			}
		}
		
		return new VersionTrackerEntry(null, [
			'versionID' => 'current',
			'userID' => $object->userID,
			'username' => $object->username,
			'data' => $payload
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTrackedData(IVersionTrackerObject $object) {
		$data = [];
		
		/** @var ArticleVersionTracker $object */
		foreach ($object->getContent() as $content) {
			$languageID = $content->languageID ?: 0;
			$data[$languageID] = [];
			
			foreach (static::$trackedProperties as $property) {
				$data[$languageID][$property] = $content->{$property};
			}
		}
		
		return $data;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isI18n(IVersionTrackerObject $object) {
		/** @var Article $object */
		return $object->isMultilingual == 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function revert(IVersionTrackerObject $object, VersionTrackerEntry $entry) {
		/** @var ArticleVersionTracker $object */
		
		// build the content data
		$properties = $this->getTrackedProperties();
		$content = [];
		foreach ($object->getArticleContents() as $articleContent) {
			$content[$articleContent->languageID ?: 0] = $entry->getPayloadForProperties($properties, $articleContent->languageID ?: 0);
		}
		
		$action = new ArticleAction([$object->getDecoratedObject()], 'update', ['content' => $content, 'isRevert' => true]);
		$action->executeAction();
	}
}
