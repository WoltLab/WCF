<?php
namespace wcf\page;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * List of deleted content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class DeletedContentListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['mod.general.canUseModeration'];
	
	/**
	 * object type object
	 * @var	\wcf\data\object\type\ObjectType
	 */
	public $objectType = null;
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = $this->objectType->getProcessor()->getObjectList();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'availableObjectTypes' => ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.deletedContent'),
			'objectType' => $this->objectType->objectType,
			'resultListTemplateName' => $this->objectType->getProcessor()->getTemplateName(),
			'resultListApplication' => $this->objectType->getProcessor()->getApplication()
		]);
	}
}
