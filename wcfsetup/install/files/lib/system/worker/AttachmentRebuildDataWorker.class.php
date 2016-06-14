<?php
namespace wcf\system\worker;
use wcf\data\attachment\AttachmentAction;
use wcf\data\attachment\AttachmentList;
use wcf\system\exception\SystemException;

/**
 * Worker implementation for updating attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 */
class AttachmentRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @inheritDoc
	 */
	protected $objectListClassName = AttachmentList::class;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 10;
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'attachment.attachmentID';
		$this->objectList->getConditionBuilder()->add('attachment.isImage = ?', [1]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		foreach ($this->objectList as $attachment) {
			try {
				$action = new AttachmentAction([$attachment], 'generateThumbnails');
				$action->executeAction();
			}
			catch (SystemException $e) {}
		}
	}
}
