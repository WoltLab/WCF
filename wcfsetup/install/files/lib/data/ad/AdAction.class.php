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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.ad
 * @category	Community Framework
 * 
 * @method	AdEditor[]	getObjects()
 * @method	AdEditor	getSingleObject()
 */
class AdAction extends AbstractDatabaseObjectAction implements ISortableAction, IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.ad.canManageAd'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.ad.canManageAd'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'toggle', 'update', 'updatePosition'];
	
	/**
	 * @inheritDoc
	 * @return	Ad
	 */
	public function create() {
		$showOrder = 0;
		if (isset($this->parameters['data']['showOrder'])) {
			$showOrder = $this->parameters['data']['showOrder'];
			unset($this->parameters['data']['showOrder']);
		}
		
		/** @var Ad $ad */
		$ad = parent::create();
		$adEditor = new AdEditor($ad);
		$adEditor->setShowOrder($showOrder);
		
		return new Ad($ad->adID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		ConditionHandler::getInstance()->deleteConditions('com.woltlab.wcf.condition.ad', $this->objectIDs);
		
		return parent::delete();
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->getObjects() as $ad) {
			$ad->update([
				'isDisabled' => $ad->isDisabled ? 0 : 1
			]);
		}
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
		
		$adList = new AdList();
		$adList->setObjectIDs($this->parameters['data']['structure'][0]);
		if ($adList->countObjects() != count($this->parameters['data']['structure'][0])) {
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
		$sql = "UPDATE	wcf".WCF_N."_ad
			SET	showOrder = ?
			WHERE	adID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$showOrder = $this->parameters['data']['offset'];
		WCF::getDB()->beginTransaction();
		foreach ($this->parameters['data']['structure'][0] as $adID) {
			$statement->execute([
				$showOrder++,
				$adID
			]);
		}
		WCF::getDB()->commitTransaction();
	}
}
