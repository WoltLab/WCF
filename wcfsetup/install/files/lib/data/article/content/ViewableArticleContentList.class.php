<?php
namespace wcf\data\article\content;
use wcf\data\article\ViewableArticleList;
use wcf\data\media\ViewableMediaList;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Represents a list of viewable article contents.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Content
 * @since	3.0
 *
 * @method	ViewableArticleContent		current()
 * @method	ViewableArticleContent[]	getObjects()
 * @method	ViewableArticleContent|null	search($objectID)
 * @property	ViewableArticleContent[]	$objects
 */
class ViewableArticleContentList extends ArticleContentList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewableArticleContent::class;
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		$imageIDs = $embeddedObjectPostIDs = $articleIDs = [];
		foreach ($this->getObjects() as $articleContent) {
			if ($articleContent->imageID) {
				$imageIDs[] = $articleContent->imageID;
			}
			if ($articleContent->teaserImageID) {
				$imageIDs[] = $articleContent->teaserImageID;
			}
			if ($articleContent->hasEmbeddedObjects) {
				$embeddedObjectPostIDs[] = $articleContent->articleContentID;
			}
			
			$articleIDs[] = $articleContent->articleID;
		}
		
		// cache images
		if (!empty($imageIDs)) {
			$mediaList = new ViewableMediaList();
			$mediaList->setObjectIDs($imageIDs);
			$mediaList->readObjects();
			$images = $mediaList->getObjects();
		}
		
		// load embedded objects
		if (!empty($embeddedObjectPostIDs)) {
			$contentLanguageID = null;
			if (count($embeddedObjectPostIDs) === 1) $contentLanguageID = reset($this->objects)->languageID;
			
			MessageEmbeddedObjectManager::getInstance()->loadObjects('com.woltlab.wcf.article.content', $embeddedObjectPostIDs, $contentLanguageID);
		}
		
		if (!empty($articleIDs)) {
			$articleList = new ViewableArticleList();
			// to prevent an infinity loop, because the list loads otherwise the article content
			$articleList->enableContentLoading(false);
			$articleList->setObjectIDs($articleIDs);
			$articleList->readObjects();
		}
		
		foreach ($this->getObjects() as $articleContent) {
			if (isset($images)) {
				if ($articleContent->imageID && isset($images[$articleContent->imageID])) {
					$articleContent->setImage($images[$articleContent->imageID]);
				}
				
				if ($articleContent->teaserImageID && isset($images[$articleContent->teaserImageID])) {
					$articleContent->setTeaserImage($images[$articleContent->teaserImageID]);
				}
			}
			
			if (isset($articleList)) {
				if ($articleList->search($articleContent->articleID) !== null) {
					$articleContent->setArticle($articleList->search($articleContent->articleID));
				}
				else {
					throw new \LogicException('Unable to find article with id "'. $articleContent->articleID .'".');
				}
			}
		}
	}
}
