<?php
namespace wcf\page;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\system\exception\IllegalLinkException;
use wcf\system\tagging\TypedTagCloud;
use wcf\system\WCF;

/**
 * Shows the a list of tagged objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class TaggedPage extends MultipleLinkPage {
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
		
		// get object type
		if (isset($_REQUEST['objectType'])) {
			$this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.tagging.taggableObject', $_REQUEST['objectType']);
			if ($this->objectType === null) {
				throw new IllegalLinkException();
			}
		}
		else {
			// use first object type
			$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.tagging.taggableObject');
			$this->objectType = reset($objectTypes);
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
			'availableObjectTypes' => ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.tagging.taggableObject'),
			'objectType' => $this->objectType->objectType,
			'resultListTemplateName' => $this->objectType->getProcessor()->getTemplateName(),
			'resultListApplication' => $this->objectType->getProcessor()->getApplication(),
			'allowSpidersToIndexThisPage' => true
		));
	}
}
