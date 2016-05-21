<?php
namespace wcf\system\bulk\processing;
use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a bulk processing action.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bulk.processing
 * @category	Community Framework
 * @since	2.2
 */
abstract class AbstractBulkProcessingAction extends AbstractObjectTypeProcessor implements IBulkProcessingAction {
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::getHTML()
	 */
	public function getHTML() {
		return '';
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::isAvailable()
	 */
	public function isAvailable() {
		return true;
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::readFormParameters()
	 */
	public function readFormParameters() {
		// does nothing
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::reset()
	 */
	public function reset() {
		// does nothing
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::validate()
	 */
	public function validate() {
		// does nothing
	}
}
