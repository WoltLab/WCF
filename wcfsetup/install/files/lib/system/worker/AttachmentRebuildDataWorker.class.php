<?php
namespace wcf\system\worker;
use wcf\data\attachment\AttachmentAction;
use wcf\system\exception\SystemException;

/**
 * Worker implementation for updating attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.worker
 * @category	Community Framework
 */
class AttachmentRebuildDataWorker extends AbstractRebuildDataWorker {
	/**
	 * @see	\wcf\system\worker\AbstractRebuildDataWorker::$objectListClassName
	 */
	protected $objectListClassName = 'wcf\data\attachment\AttachmentList';
	
	/**
	 * @see	\wcf\system\worker\AbstractWorker::$limit
	 */
	protected $limit = 10;
	
	/**
	 * @see	\wcf\system\worker\AbstractRebuildDataWorker::initObjectList
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlOrderBy = 'attachment.attachmentID';
		$this->objectList->getConditionBuilder()->add('attachment.isImage = ?', array(1));
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::execute()
	 */
	public function execute() {
		parent::execute();
		
		foreach ($this->objectList as $attachment) {
			try {
				$action = new AttachmentAction(array($attachment), 'generateThumbnails');
				$action->executeAction();
			}
			catch (SystemException $e) {}
		}
	}
}
