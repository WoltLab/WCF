<?php
namespace wcf\data\ad;
use wcf\data\condition\Condition;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\system\condition\ConditionHandler;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents an ad.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Ad
 *
 * @property-read	integer		$adID		unique id of the ad
 * @property-read	integer		$objectTypeID	id of the `com.woltlab.wcf.adLocation` object type
 * @property-read	string		$adName		name of the ad shown in ACP
 * @property-read	string		$ad		ad text
 * @property-read	integer		$isDisabled	is `1` if the ad is disabled and thus not shown, otherwise `0`
 * @property-read	integer		$showOrder	position of the ad in relation to the other ads at the same location
 */
class Ad extends DatabaseObject implements IRouteController {
	/**
	 * Returns the conditions of the ad.
	 * 
	 * @return	Condition[]
	 */
	public function getConditions() {
		return ConditionHandler::getInstance()->getConditions('com.woltlab.wcf.condition.ad', $this->adID);
	}
	
	/**
	 * Returns the location of the ad.
	 * 
	 * @return	string
	 */
	public function getLocation() {
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
		
		$location = WCF::getLanguage()->get('wcf.acp.ad.location.'.$objectType->objectType);
		/** @noinspection PhpUndefinedFieldInspection */
		if ($objectType->categoryname != 'com.woltlab.wcf.global') {
			/** @noinspection PhpUndefinedFieldInspection */
			$location = WCF::getLanguage()->get('wcf.acp.ad.location.category.'.$objectType->categoryname).': '.$location;
		}
		
		return $location;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->adName;
	}
}
