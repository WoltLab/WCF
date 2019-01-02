<?php
namespace wcf\page;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\data\tag\TagList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\tagging\TypedTagCloud;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the a list of tagged objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class CombinedTaggedPage extends TaggedPage {
	public $tags = [];
	public $tagIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		MultipleLinkPage::readParameters();
		
		if (isset($_GET['tagIDs']) && is_array($this->tagIDs)) $this->tagIDs = ArrayUtil::toIntegerArray($_GET['tagIDs']);
		if (empty($this->tagIDs)) {
			throw new IllegalLinkException();
		}
		else if (count($this->tagIDs) > SEARCH_MAX_COMBINED_TAGS) {
			throw new PermissionDeniedException();
		}
		
		$tagList = new TagList();
		$tagList->getConditionBuilder()->add('tagID IN (?)', [$this->tagIDs]);
		$tagList->readObjects();
		
		$this->tags = $tagList->getObjects();
		if (empty($this->tags)) {
			throw new IllegalLinkException();
		}
		
		$this->availableObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.tagging.taggableObject');
		foreach ($this->availableObjectTypes as $key => $objectType) {
			if (!$objectType->validateOptions() || !$objectType->validatePermissions()) {
				unset($this->availableObjectTypes[$key]);
			}
		}
		
		if (empty($this->availableObjectTypes)) {
			throw new IllegalLinkException();
		}
		
		if (isset($_REQUEST['objectType'])) {
			$objectType = StringUtil::trim($_REQUEST['objectType']);
			if (!isset($this->availableObjectTypes[$objectType])) {
				throw new IllegalLinkException();
			}
			$this->objectType = $this->availableObjectTypes[$objectType];
		}
		else {
			// No object type provided, use the first object type.
			$this->objectType = reset($this->availableObjectTypes);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = $this->objectType->getProcessor()->getObjectListFor($this->tags);
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
			@header('HTTP/1.1 404 Not Found');
		}
	}
}
