<?php
namespace wcf\data\ad;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\IToggleAction;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes ad-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.ad
 * @category	Community Framework
 */
class AdAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.ad.canManageAd');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.ad.canManageAd');
	
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
		
		$ad = parent::create();
		$adEditor = new AdEditor($ad);
		$adEditor->setShowOrder($showOrder);
		
		return new Ad($ad->adID);
	}
	
	/**
	 * @see	\wcf\data\IDeleteAction::delete()
	 */
	public function delete() {
		ConditionHandler::getInstance()->deleteConditions('com.woltlab.wcf.condition.ad', $this->objectIDs);
		
		return parent::delete();
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::toggle()
	 */
	public function toggle() {
		foreach ($this->objects as $ad) {
			$ad->update(array(
				'isDisabled' => $ad->isDisabled ? 0 : 1
			));
		}
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
		
		$adList = new AdList();
		$adList->getConditionBuilder()->add('ad.adID IN (?)', array($this->parameters['data']['structure'][0]));
		if ($adList->countObjects() != count($this->parameters['data']['structure'][0])) {
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
		$sql = "UPDATE	wcf".WCF_N."_ad
			SET	showOrder = ?
			WHERE	adID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$showOrder = $this->parameters['data']['offset'];
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'][0] as $adID) {
			$statement->execute(array(
				$showOrder++,
				$adID
			));
		}
		WCF::getDB()->commitTransaction();
	}
}
