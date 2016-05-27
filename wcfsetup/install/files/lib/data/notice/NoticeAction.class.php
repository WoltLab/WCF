<?php
namespace wcf\data\notice;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\UserInputException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes notice-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.notice
 * @category	Community Framework
 * 
 * @method	NoticeEditor[]		getObjects()
 * @method	NoticeEditor		getSingleObject()
 */
class NoticeAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['dismiss'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.notice.canManageNotice'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.notice.canManageNotice'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'toggle', 'update', 'updatePosition'];
	
	/**
	 * @inheritDoc
	 * @return	Notice
	 */
	public function create() {
		$showOrder = 0;
		if (isset($this->parameters['data']['showOrder'])) {
			$showOrder = $this->parameters['data']['showOrder'];
			unset($this->parameters['data']['showOrder']);
		}
		
		/** @var Notice $notice */
		$notice = parent::create();
		$noticeEditor = new NoticeEditor($notice);
		$noticeEditor->setShowOrder($showOrder);
		
		return new Notice($notice->noticeID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		ConditionHandler::getInstance()->deleteConditions('com.woltlab.wcf.condition.notice', $this->objectIDs);
		
		return parent::delete();
	}
	
	/**
	 * Dismisses a certain notice.
	 * 
	 * @return	integer[]
	 */
	public function dismiss() {
		if (WCF::getUser()->userID) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_notice_dismissed
							(noticeID, userID)
				VALUES			(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				reset($this->objectIDs),
				WCF::getUser()->userID
			]);
			
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'dismissedNotices');
		}
		else {
			$dismissedNotices = WCF::getSession()->getVar('dismissedNotices');
			if ($dismissedNotices !== null) {
				$dismissedNotices = @unserialize($dismissedNotices);
				$dismissedNotices[] = reset($this->objectIDs);
			}
			else {
				$dismissedNotices = [
					reset($this->objectIDs)
				];
			}
			
			WCF::getSession()->register('dismissedNotices', serialize($dismissedNotices));
		}
		
		return [
			'noticeID' => reset($this->objectIDs)
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->getObjects() as $notice) {
			$notice->update([
				'isDisabled' => $notice->isDisabled ? 0 : 1
			]);
		}
	}
	
	/**
	 * Validates the 'dismiss' action.
	 */
	public function validateDismiss() {
		$this->getSingleObject();
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		parent::validateUpdate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateUpdatePosition() {
		WCF::getSession()->checkPermissions($this->permissionsUpdate);
		
		if (!isset($this->parameters['data']['structure']) || !is_array($this->parameters['data']['structure'])) {
			throw new UserInputException('structure');
		}
		
		$noticeList = new NoticeList();
		$noticeList->setObjectIDs($this->parameters['data']['structure'][0]);
		if ($noticeList->countObjects() != count($this->parameters['data']['structure'][0])) {
			throw new UserInputException('structure');
		}
		
		$this->readInteger('offset', true, 'data');
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		parent::update();
		
		if (count($this->objects) == 1 && isset($this->parameters['data']['showOrder']) && $this->parameters['data']['showOrder'] != reset($this->objects)->showOrder) {
			reset($this->objects)->setShowOrder($this->parameters['data']['showOrder']);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function updatePosition() {
		$sql = "UPDATE	wcf".WCF_N."_notice
			SET	showOrder = ?
			WHERE	noticeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$showOrder = $this->parameters['data']['offset'];
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'][0] as $noticeID) {
			$statement->execute([
				$showOrder++,
				$noticeID
			]);
		}
		WCF::getDB()->commitTransaction();
	}
}
