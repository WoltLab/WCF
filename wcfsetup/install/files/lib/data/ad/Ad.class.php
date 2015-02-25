<?php
namespace wcf\data\ad;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\system\condition\ConditionHandler;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents an ad.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.ad
 * @category	Community Framework
 */
class Ad extends DatabaseObject implements IRouteController {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'adID';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'ad';
	
	/**
	 * Returns the conditions of the ad.
	 * 
	 * @return	array<\wcf\data\condition\Condition>
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
		if ($objectType->categoryname != 'com.woltlab.wcf.global') {
			$location = WCF::getLanguage()->get('wcf.acp.ad.location.category.'.$objectType->categoryname).': '.$location;
		}
		
		return $location;
	}
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return $this->adName;
	}
}
