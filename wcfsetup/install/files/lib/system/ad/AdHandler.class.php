<?php
namespace wcf\system\ad;
use wcf\data\ad\Ad;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\AdCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles ads.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Ads
 */
class AdHandler extends SingletonFactory {
	/**
	 * list of ad objects grouped by ad location
	 * @var	Ad[][]
	 */
	protected $ads = [];
	
	/**
	 * list of ad location object types
	 * @var	ObjectType[]
	 */
	protected $objectTypes = [];
	
	/**
	 * Returns the ad output for the given ad location.
	 * 
	 * @param	string		$adLocation
	 * @return	string
	 * @throws	SystemException
	 */
	public function getAds($adLocation) {
		if (!isset($this->objectTypes[$adLocation])) {
			throw new SystemException("Unknown ad location '".$adLocation."'");
		}
		
		if (!isset($this->ads[$this->objectTypes[$adLocation]->objectTypeID])) {
			return '';
		}
		
		$output = '';
		foreach ($this->ads[$this->objectTypes[$adLocation]->objectTypeID] as $ad) {
			$conditions = $ad->getConditions();
			foreach ($conditions as $condition) {
				if (!$condition->getObjectType()->getProcessor()->showContent($condition)) {
					continue 2;
				}
			}
			
			$output .= '<div>' . $ad->ad . '</div>';
		}
		
		if (!empty($output)) {
			return '<div class="wcfAdLocation' . ($this->objectTypes[$adLocation]->cssclassname ? (' ' . $this->objectTypes[$adLocation]->cssclassname) : '') . '">' . $output . '</div>';
		}
		
		return '';
	}
	
	/**
	 * Returns all available ad location object types.
	 * 
	 * @param	string|null	$categoryName
	 * @return	ObjectType[]
	 */
	public function getLocationObjectTypes($categoryName = null) {
		if ($categoryName === null) {
			return $this->objectTypes;
		}
		
		$objectTypes = [];
		foreach ($this->objectTypes as $key => $objectType) {
			if ($objectType->categoryname == $categoryName) {
				$objectTypes[$key] = $objectType;
			}
		}
		
		return $objectTypes;
	}
	
	/**
	 * Returns the list of available locations used to be used for selections.
	 * 
	 * @return	string[]
	 */
	public function getLocationSelection() {
		$objectTypes = $this->objectTypes;
		
		// filter by options
		foreach ($objectTypes as $objectTypeName => $objectType) {
			if (!$objectType->validateOptions()) {
				unset($objectTypes[$objectTypeName]);
			}
		}
		
		$selection = [];
		foreach ($objectTypes as $objectType) {
			$categoryName = WCF::getLanguage()->get('wcf.acp.ad.location.category.'.$objectType->categoryname);
			if (!isset($selection[$categoryName])) {
				$selection[$categoryName] = [];
			}
			
			$selection[$categoryName][$objectType->objectTypeID] = WCF::getLanguage()->get('wcf.acp.ad.location.'.$objectType->objectType);
		}
		
		foreach ($selection as &$subSelection) {
			asort($subSelection);
		}
		
		$globalCategory = WCF::getLanguage()->get('wcf.acp.ad.location.category.com.woltlab.wcf.global');
		$globalLocations = $selection[$globalCategory];
		unset($selection[$globalCategory]);
		
		ksort($selection);
		
		$selection = array_merge([
			$globalCategory => $globalLocations
		], $selection);
		
		return $selection;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->ads = AdCacheBuilder::getInstance()->getData();
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.adLocation');
	}
}
