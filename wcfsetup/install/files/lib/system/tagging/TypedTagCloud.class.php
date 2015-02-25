<?php
namespace wcf\system\tagging;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\TypedTagCloudCacheBuilder;

/**
 * This class provides the function to filter the tag cloud by object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.tagging
 * @category	Community Framework
 */
class TypedTagCloud extends TagCloud {
	/**
	 * object type ids
	 * @var	array<integer>
	 */
	protected $objectTypeIDs = array();
	
	/**
	 * Contructs a new TypedTagCloud object.
	 * 
	 * @param	string		$objectType
	 * @param	array<integer>	$languageIDs
	 */
	public function __construct($objectType, array $languageIDs = array()) {
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.tagging.taggableObject', $objectType);
		$this->objectTypeIDs[] = $objectTypeObj->objectTypeID;
		
		parent::__construct($languageIDs);
	}
	
	/**
	 * Loads the tag cloud cache.
	 */
	protected function loadCache() {
		$this->tags = TypedTagCloudCacheBuilder::getInstance()->getData(array(
			'languageIDs' => $this->languageIDs,
			'objectTypeIDs' => $this->objectTypeIDs
		));
	}
}
