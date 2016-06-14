<?php
namespace wcf\system\tagging;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\TypedTagCloudCacheBuilder;

/**
 * This class provides the function to filter the tag cloud by object types.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Tagging
 */
class TypedTagCloud extends TagCloud {
	/**
	 * object type ids
	 * @var	integer[]
	 */
	protected $objectTypeIDs = [];
	
	/**
	 * Contructs a new TypedTagCloud object.
	 * 
	 * @param	string		$objectType
	 * @param	integer[]	$languageIDs
	 */
	public function __construct($objectType, array $languageIDs = []) {
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.tagging.taggableObject', $objectType);
		$this->objectTypeIDs[] = $objectTypeObj->objectTypeID;
		
		parent::__construct($languageIDs);
	}
	
	/**
	 * Loads the tag cloud cache.
	 */
	protected function loadCache() {
		$this->tags = TypedTagCloudCacheBuilder::getInstance()->getData([
			'languageIDs' => $this->languageIDs,
			'objectTypeIDs' => $this->objectTypeIDs
		]);
	}
}
