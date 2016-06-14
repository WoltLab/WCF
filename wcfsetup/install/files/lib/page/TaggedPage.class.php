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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class TaggedPage extends MultipleLinkPage {
	/**
	 * list of available taggable object types
	 * @var	ObjectType[]
	 */
	public $availableObjectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_TAGGING'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.tag.canViewTag'];
	
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = $this->objectType->getProcessor()->getObjectList($this->tag);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->tagCloud = new TypedTagCloud($this->objectType->objectType);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'tag' => $this->tag,
			'tags' => $this->tagCloud->getTags(100),
			'availableObjectTypes' => $this->availableObjectTypes,
			'objectType' => $this->objectType->objectType,
			'resultListTemplateName' => $this->objectType->getProcessor()->getTemplateName(),
			'resultListApplication' => $this->objectType->getProcessor()->getApplication()
		]);
		
		if (count($this->objectList) === 0) {
			@header('HTTP/1.0 404 Not Found');
		}
	}
}
