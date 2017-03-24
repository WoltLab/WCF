<?php
namespace wcf\system\version;
use wcf\data\article\Article;
use wcf\data\article\ArticleList;
use wcf\data\article\ArticleVersionTracker;
use wcf\data\IVersionTrackerObject;

/**
 * Version tracker object type provider implementation for articles.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Version
 */
class ArticleVersionTrackerProvider extends AbstractVersionTrackerProvider {
	/**
	 * @inheritDoc
	 */
	public $className = Article::class;
	
	/**
	 * @inheritDoc
	 */
	public $listClassName = ArticleList::class;
	
	/**
	 * @inheritDoc
	 */
	public static $trackedProperties = ['content', 'teaser', 'title'];
	
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
}