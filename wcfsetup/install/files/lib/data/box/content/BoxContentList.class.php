<?php
namespace wcf\data\box\content;
use wcf\data\media\ViewableMediaList;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of box content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		if ($this->imageLoading) {
			$imageIDs = [];
			foreach ($this->getObjects() as $boxContent) {
				if ($boxContent->imageID) {
					$imageIDs[] = $boxContent->imageID;
				}
			}
			
			// cache images
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
	}
	
	
	/**
	 * Enables/disables the loading of box content images.
	 *
	 * @param	boolean		$enable
	 */
	public function enableImageLoading($enable = true) {
		$this->imageLoading = $enable;
	}
}
