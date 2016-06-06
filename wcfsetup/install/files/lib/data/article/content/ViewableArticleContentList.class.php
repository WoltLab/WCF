<?php
namespace wcf\data\article\content;
use wcf\data\media\ViewableMediaList;

/**
 * Represents a list of viewable article contents.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.article
 * @category	Community Framework
 * @since	2.2
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
		
		$imageIDs = [];
		foreach ($this->getObjects() as $articleContent) {
			if ($articleContent->imageID) {
				$imageIDs[] = $articleContent->imageID;
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
			}
		}
	}
}
