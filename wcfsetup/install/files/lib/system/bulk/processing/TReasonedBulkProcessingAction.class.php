<?php
namespace wcf\system\bulk\processing;
use wcf\util\StringUtil;
use wcf\system\WCF;

/**
 * Trait for bulk processing actions allowing to enter a reason for executing the action.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bulk.processing
 * @category	Community Framework
 * @since	2.2
 */
trait TReasonedBulkProcessingAction {
	/**
	 * reason
	 * @var	string
	 */
	protected $reason = '';
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::getHTML()
	 */
	public function getHTML() {
		return WCF::getTPL()->fetch('reasonedBulkProcessingAction', 'wcf', [
			'reason' => $this->reason,
			'reasonFieldName' => $this->getReasonFieldName()
		]);
	}
	
	/**
	 * Returns the name of the reason field.
	 * 
	 * @return	string
	 */
	abstract protected function getReasonFieldName();
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST[$this->getReasonFieldName()])) $this->reason = StringUtil::trim($_POST[$this->getReasonFieldName()]);
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::reset()
	 */
	public function reset() {
		$this->reason = '';
	}
}
