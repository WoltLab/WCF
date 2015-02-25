<?php
namespace wcf\page;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * List of deleted content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class DeletedContentListPage extends MultipleLinkPage {
	/**
	 * @see	\wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('mod.general.canUseModeration');
	
	/**
	 * object type object
	 * @var	\wcf\data\object\type\ObjectType
	 */
	public $objectType = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get object type
		if (isset($_REQUEST['objectType'])) {
			$this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.deletedContent', $_REQUEST['objectType']);
		}
		else {
			// use first object type
			$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.deletedContent');
			if (!empty($objectTypes)) $this->objectType = reset($objectTypes);
		}
		
		if ($this->objectType === null) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::readParameters()
	 */
	protected function initObjectList() {
		$this->objectList = $this->objectType->getProcessor()->getObjectList();
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'availableObjectTypes' => ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.deletedContent'),
			'objectType' => $this->objectType->objectType,
			'resultListTemplateName' => $this->objectType->getProcessor()->getTemplateName(),
			'resultListApplication' => $this->objectType->getProcessor()->getApplication()
		));
	}
}
