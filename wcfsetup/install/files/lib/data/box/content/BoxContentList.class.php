<?php
namespace wcf\data\box\content;
use wcf\data\media\ViewableMediaList;
use wcf\data\DatabaseObjectList;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Represents a list of box content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Box\Content
 * @since	3.0
 *
 * @method	BoxContent		current()
 * @method	BoxContent[]	        getObjects()
 * @method	BoxContent|null	        search($objectID)
 * @property	BoxContent[]	        $objects
 */
class BoxContentList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = BoxContent::class;
	
	/**
	 * enables/disables the loading of box content images
	 * @var	boolean
	 */
	protected $imageLoading = false;
	
	/**
	 * enables/disables the loading of embedded objects
	 * @var	boolean
	 */
	protected $embeddedObjectLoading = false;
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		$imageIDs = $embeddedObjectBoxContentIDs = [];
		foreach ($this->getObjects() as $boxContent) {
			if ($boxContent->imageID) {
				$imageIDs[] = $boxContent->imageID;
			}
			
			if ($boxContent->hasEmbeddedObjects) {
				$embeddedObjectBoxContentIDs[] = $boxContent->boxContentID;
			}
		}
		
		if ($this->imageLoading) {
			if (!empty($imageIDs)) {
				$mediaList = new ViewableMediaList();
				$mediaList->setObjectIDs($imageIDs);
				$mediaList->readObjects();
				$images = $mediaList->getObjects();
				
				foreach ($this->getObjects() as $boxContent) {
					if ($boxContent->imageID && isset($images[$boxContent->imageID])) {
						$boxContent->setImage($images[$boxContent->imageID]);
					}
				}
			}
		}
		
		if ($this->embeddedObjectLoading) {
			if (!empty($embeddedObjectBoxContentIDs)) {
				MessageEmbeddedObjectManager::getInstance()->loadObjects('com.woltlab.wcf.box.content', $embeddedObjectBoxContentIDs);
			}
		}
	}
	
	/**
	 * Enables/disables the loading of box content images.
	 *
	 * @param	boolean		$enable
	 */
	public function enableImageLoading($enable = true) {
		$this->imageLoading = $enable;
	}
	
	/**
	 * Enables/disables the loading of embedded objects.
	 *
	 * @param	boolean		$enable
	 */
	public function enableEmbeddedObjectLoading($enable = true) {
		$this->embeddedObjectLoading = $enable;
	}
}
