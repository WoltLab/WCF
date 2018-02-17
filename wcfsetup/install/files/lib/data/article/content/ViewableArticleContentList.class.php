<?php
namespace wcf\data\article\content;
use wcf\data\media\ViewableMediaList;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Represents a list of viewable article contents.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
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
		
		$imageIDs = $embeddedObjectPostIDs = [];
		foreach ($this->getObjects() as $articleContent) {
			if ($articleContent->imageID) {
				$imageIDs[] = $articleContent->imageID;
			}
			if ($articleContent->thumbnailImageID) {
				$imageIDs[] = $articleContent->thumbnailImageID;
			}
			if ($articleContent->hasEmbeddedObjects) {
				$embeddedObjectPostIDs[] = $articleContent->articleContentID;
			}
		}
		
		// cache images
		if (!empty($imageIDs)) {
			$mediaList = new ViewableMediaList();
			$mediaList->setObjectIDs($imageIDs);
			$mediaList->readObjects();
			$images = $mediaList->getObjects();
			
			foreach ($this->getObjects() as $articleContent) {
				if ($articleContent->imageID && isset($images[$articleContent->imageID])) {
					$articleContent->setImage($images[$articleContent->imageID]);
				}
				if ($articleContent->teaserImageID && isset($images[$articleContent->teaserImageID])) {
					$articleContent->setTeaserImage($images[$articleContent->teaserImageID]);
				}
			}
		}
		
		// load embedded objects
		if (!empty($embeddedObjectPostIDs)) {
			$contentLanguageID = null;
			if (count($embeddedObjectPostIDs) === 1) $contentLanguageID = reset($this->objects)->languageID;
			
			MessageEmbeddedObjectManager::getInstance()->loadObjects('com.woltlab.wcf.article.content', $embeddedObjectPostIDs, $contentLanguageID);
		}
	}
}
