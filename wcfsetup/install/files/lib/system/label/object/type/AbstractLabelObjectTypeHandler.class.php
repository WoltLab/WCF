<?php
namespace wcf\system\label\object\type;
use wcf\system\SingletonFactory;

/**
 * Abstract implementation of a label object type handler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.label.object.type
 * @category	Community Framework
 */
abstract class AbstractLabelObjectTypeHandler extends SingletonFactory implements ILabelObjectTypeHandler {
	/**
	 * label object type container
	 * @var	\wcf\system\label\object\type\LabelObjectTypeContainer
	 */
	public $container = null;
	
	/**
	 * object type id
	 * @var	integer
	 */
	public $objectTypeID = 0;
	
	/**
	 * @see	\wcf\system\label\object\type\ILabelObjectTypeHandler::setObjectTypeID()
	 */
	public function setObjectTypeID($objectTypeID) {
		$this->objectTypeID = $objectTypeID;
	}
	
	/**
	 * @see	\wcf\system\label\object\type\ILabelObjectTypeHandler::getObjectTypeID()
	 */
	public function getObjectTypeID() {
		return $this->objectTypeID;
	}
	
	/**
	 * @see	\wcf\system\label\object\type\ILabelObjectTypeHandler::getContainer()
	 */
	public function getContainer() {
		return $this->container;
	}
}
