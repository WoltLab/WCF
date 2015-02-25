<?php
namespace wcf\data\notice;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\system\exception\UserInputException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes notice-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.notice
 * @category	Community Framework
 */
class NoticeAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('dismiss');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.notice.canManageNotice');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.notice.canManageNotice');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('create', 'delete', 'toggle', 'update', 'updatePosition');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$showOrder = 0;
		if (isset($this->parameters['data']['showOrder'])) {
			$showOrder = $this->parameters['data']['showOrder'];
			unset($this->parameters['data']['showOrder']);
		}
		
		$notice = parent::create();
		$noticeEditor = new NoticeEditor($notice);
		$noticeEditor->setShowOrder($showOrder);
		
		return new Notice($notice->noticeID);
	}
	
	/**
	 * Dismisses a certain notice.
	 * 
	 * @return	array<integer>
	 */
	public function dismiss() {
		if (WCF::getUser()->userID) {
			$sql = "INSERT INTO	wcf".WCF_N."_notice_dismissed
						(noticeID, userID)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				reset($this->objectIDs),
				WCF::getUser()->userID
			));
			
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'dismissedNotices');
		}
		else {
			$dismissedNotices = WCF::getSession()->getVar('dismissedNotices');
			if ($dismissedNotices !== null) {
				$dismissedNotices = @unserialize($dismissedNotices);
				$dismissedNotices[] = reset($this->objectIDs);
			}
			else {
				$dismissedNotices = array(
					reset($this->objectIDs)
				);
			}
			
			WCF::getSession()->register('dismissedNotices', serialize($dismissedNotices));
		}
		
		return array(
			'noticeID' => reset($this->objectIDs)
		);
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::toggle()
	 */
	public function toggle() {
		foreach ($this->objects as $notice) {
			$notice->update(array(
				'isDisabled' => $notice->isDisabled ? 0 : 1
			));
		}
	}
	
	/**
	 * Validates the 'dismiss' action.
	 */
	public function validateDismiss() {
		$this->getSingleObject();
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::validateToggle()
	 */
	public function validateToggle() {
		parent::validateUpdate();
	}
	
	/**
	 * @see	\wcf\data\ISortableAction::validateUpdatePosition()
	 */
	public function validateUpdatePosition() {
		WCF::getSession()->checkPermissions($this->permissionsUpdate);
		
		if (!isset($this->parameters['data']['structure']) || !is_array($this->parameters['data']['structure'])) {
			throw new UserInputException('structure');
		}
		
		$noticeList = new NoticeList();
		$noticeList->getConditionBuilder()->add('notice.noticeID IN (?)', array($this->parameters['data']['structure'][0]));
		if ($noticeList->countObjects() != count($this->parameters['data']['structure'][0])) {
			throw new UserInputException('structure');
		}
		
		$this->readInteger('offset', true, 'data');
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		parent::update();
		
		if (count($this->objects) == 1 && isset($this->parameters['data']['showOrder']) && $this->parameters['data']['showOrder'] != reset($this->objects)->showOrder) {
			reset($this->objects)->setShowOrder($this->parameters['data']['showOrder']);
		}
	}
	
	/**
	 * @see	\wcf\data\ISortableAction::updatePosition()
	 */
	public function updatePosition() {
		$sql = "UPDATE	wcf".WCF_N."_notice
			SET	showOrder = ?
			WHERE	noticeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$showOrder = $this->parameters['data']['offset'];
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'][0] as $noticeID) {
			$statement->execute(array(
				$showOrder++,
				$noticeID
			));
		}
		WCF::getDB()->commitTransaction();
	}
}
