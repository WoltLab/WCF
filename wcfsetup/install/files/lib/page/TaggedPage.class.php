<?php
namespace wcf\page;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\system\exception\IllegalLinkException;
use wcf\system\tagging\TypedTagCloud;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the a list of tagged objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class TaggedPage extends MultipleLinkPage {
	/**
	 * list of available taggable object types
	 * @var	ObjectType[]
	 */
	public $availableObjectTypes = array();
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_TAGGING');
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('user.tag.canViewTag');
	
	/**
	 * tag id
	 * @var	integer
	 */
	public $tagID = 0;
	
	/**
	 * tag object
	 * @var	\wcf\data\tag\Tag
	 */
	public $tag = null;
	
	/**
	 * object type object
	 * @var	\wcf\data\object\type\ObjectType
	 */
	public $objectType = null;
	
	/**
	 * tag cloud
	 * @var	\wcf\system\tagging\TypedTagCloud
	 */
	public $tagCloud = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get tag id
		if (isset($_REQUEST['id'])) $this->tagID = intval($_REQUEST['id']);
		$this->tag = new Tag($this->tagID);
		if (!$this->tag->tagID) {
			throw new IllegalLinkException();
		}
		
		// filter taggable object types by options and permissions
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.tagging.taggableObject');
		foreach ($this->availableObjectTypes as $key => $objectType) {
			if (!$objectType->validateOptions() || !$objectType->validatePermissions()) {
				unset($this->availableObjectTypes[$key]);
			}
		}
		
		if (empty($this->availableObjectTypes)) {
			throw new IllegalLinkException();
		}
		
		// get object type
		if (isset($_REQUEST['objectType'])) {
			$objectType = StringUtil::trim($_REQUEST['objectType']);
			if (!isset($this->availableObjectTypes[$objectType])) {
				throw new IllegalLinkException();
			}
			$this->objectType = $this->availableObjectTypes[$objectType];
		}
		else {
			// use first object type
			$this->objectType = reset($this->availableObjectTypes);
		}
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::readParameters()
	 */
	protected function initObjectList() {
		$this->objectList = $this->objectType->getProcessor()->getObjectList($this->tag);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->tagCloud = new TypedTagCloud($this->objectType->objectType);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'tag' => $this->tag,
			'tags' => $this->tagCloud->getTags(100),
			'availableObjectTypes' => $this->availableObjectTypes,
			'objectType' => $this->objectType->objectType,
			'resultListTemplateName' => $this->objectType->getProcessor()->getTemplateName(),
			'resultListApplication' => $this->objectType->getProcessor()->getApplication()
		));
		
		if (count($this->objectList) === 0) {
			@header('HTTP/1.0 404 Not Found');
		}
	}
}
