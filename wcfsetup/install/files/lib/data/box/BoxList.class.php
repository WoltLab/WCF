<?php
namespace wcf\data\box;
use wcf\data\box\content\BoxContentList;
use wcf\data\DatabaseObjectList;
use wcf\system\WCF;

/**
 * Represents a list of boxes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Box
 * @since	3.0
 *
 * @method	Box		current()
 * @method	Box[]		getObjects()
 * @method	Box|null	search($objectID)
 * @property	Box[]		$objects
 */
class BoxList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Box::class;
	
	/**
	 * enables/disables the loading of box content objects
	 * @var	boolean
	 */
	protected $contentLoading = false;
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		// get box content
		if ($this->contentLoading) {
			$boxIDs = [];
			foreach ($this->getObjects() as $box) {
				if ($box->boxType != 'system' && $box->boxType != 'menu') {
					$boxIDs[] = $box->boxID;
				}
			}
			
			if (!empty($boxIDs)) {
				$contentList = new BoxContentList();
				$contentList->enableImageLoading();
				$contentList->enableEmbeddedObjectLoading();
				$contentList->getConditionBuilder()->add('box_content.boxID IN (?)', [$this->objectIDs]);
				$contentList->getConditionBuilder()->add('(box_content.languageID IS NULL OR box_content.languageID = ?)', [WCF::getLanguage()->languageID]);
				$contentList->readObjects();
				foreach ($contentList as $boxContent) {
					$this->objects[$boxContent->boxID]->setBoxContents([$boxContent->languageID ?: 0 => $boxContent]);
				}
			}
		}
	}
	
	/**
	 * Enables/disables the loading of box content objects.
	 *
	 * @param	boolean		$enable
	 */
	public function enableContentLoading($enable = true) {
		$this->contentLoading = $enable;
	}
}
