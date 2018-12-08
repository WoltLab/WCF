<?php
namespace wcf\system\worker;
use wcf\data\media\MediaAction;
use wcf\data\media\MediaList;
use wcf\system\exception\SystemException;

/**
 * Worker implementation for updating media thumbnails.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 * 
 * @method	MediaList	getObjectList()
 */
class MediaRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = MediaList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 10;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'media.mediaID';
		$this->objectList->getConditionBuilder()->add('media.isImage = ?', [1]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		foreach ($this->objectList as $media) {
			try {
				(new MediaAction([$media], 'generateThumbnails'))->executeAction();
			}
			catch (SystemException $e) {}
		}
	}
}
