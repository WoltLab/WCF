<?php
namespace wcf\data\paid\subscription;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * Executes paid subscription-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.paid.subscription
 * @category	Community Framework
 */
class PaidSubscriptionAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.paidSubscription.canManageSubscription');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.paidSubscription.canManageSubscription');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('create', 'delete', 'toggle', 'update');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$showOrder = 0;
		if (isset($this->parameters['data']['showOrder'])) {
			$showOrder = $this->parameters['data']['showOrder'];
			unset($this->parameters['data']['showOrder']);
		}
		
		$subscription = parent::create();
		$editor = new PaidSubscriptionEditor($subscription);
		$editor->setShowOrder($showOrder);
		
		return new PaidSubscription($subscription->subscriptionID);
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
	 * @see	\wcf\data\IToggleAction::toggle()
	 */
	public function toggle() {
		foreach ($this->objects as $object) {
			$object->update(array(
				'isDisabled' => $object->isDisabled ? 0 : 1
			));
		}
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::validateToggle()
	 */
	public function validateToggle() {
		parent::validateUpdate();
	}
}
