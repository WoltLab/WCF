<?php
namespace wcf\data\article\content;
use wcf\data\media\ViewableMediaList;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Represents a list of viewable article contents.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Content
 * @since	3.0
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
			
			/** @var ViewableArticleContent $articleContent */
			foreach ($this->getObjects() as $articleContent) {
				if ($articleContent->imageID && isset($images[$articleContent->imageID])) {
					$articleContent->setImage($images[$articleContent->imageID]);
				}
			}
		}
		
		// load embedded objects
		if (!empty($embeddedObjectPostIDs)) {
			MessageEmbeddedObjectManager::getInstance()->loadObjects('com.woltlab.wcf.article.content', $embeddedObjectPostIDs);
		}
	}
}
