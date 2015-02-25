<?php
namespace wcf\system\dashboard;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\object\type\ObjectTypeCache;
use wcf\page\IPage;
use wcf\system\cache\builder\DashboardBoxCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * Handles dashboard boxes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard
 * @category	Community Framework
 */
class DashboardHandler extends SingletonFactory {
	/**
	 * list of cached dashboard boxes
	 * @var	array<\wcf\data\dashboard\box\DashboardBox>
	 */
	protected $boxCache = null;
	
	/**
	 * configuration options for pages
	 * @var	array<array>
	 */
	protected $pageCache = null;
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->boxCache = DashboardBoxCacheBuilder::getInstance()->getData(array(), 'boxes');
		$this->pageCache = DashboardBoxCacheBuilder::getInstance()->getData(array(), 'pages');
	}
	
	/**
	 * Loads the active dashboard boxes for the given object type and page.
	 * 
	 * @param	string		$objectType
	 * @param	\wcf\page\IPage	$page
	 */
	public function loadBoxes($objectType, IPage $page) {
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.dashboardContainer', $objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Unable to find object type '".$objectType."' for definition 'com.woltlab.wcf.user.dashboardContainer'");
		}
		
		$boxIDs = array();
		if (isset($this->pageCache[$objectTypeObj->objectTypeID]) && is_array($this->pageCache[$objectTypeObj->objectTypeID])) {
			foreach ($this->pageCache[$objectTypeObj->objectTypeID] as $boxID) {
				$boxIDs[] = $boxID;
			}
		}
		
		$contentTemplate = $sidebarTemplate = '';
		foreach ($boxIDs as $boxID) {
			$className = $this->boxCache[$boxID]->className;
			if (!ClassUtil::isInstanceOf($className, 'wcf\system\dashboard\box\IDashboardBox')) {
				throw new SystemException("'".$className."' does not implement 'wcf\system\dashboard\box\IDashboardBox'");
			}
			
			$boxObject = new $className();
			$boxObject->init($this->boxCache[$boxID], $page);
			
			if ($this->boxCache[$boxID]->boxType == 'content') {
				$contentTemplate .= $boxObject->getTemplate();
			}
			else {
				$sidebarTemplate .= $boxObject->getTemplate();
			}
		}
		
		WCF::getTPL()->assign(array(
			'__boxContent' => $contentTemplate,
			'__boxSidebar' => $sidebarTemplate
		));
	}
	
	/**
	 * Sets default values upon installation, you should not call this method
	 * under any other circumstances. If you do not specify a list of box names,
	 * all boxes will be assigned as disabled for given object type.
	 * 
	 * @param	string		$objectType
	 * @param	array<names>	$enableBoxNames
	 */
	public static function setDefaultValues($objectType, array $enableBoxNames = array()) {
		$objectTypeID = 0;
		
		// no boxes given, aborting
		if (empty($enableBoxNames)) {
			return;
		}
		
		// get object type id (cache might be outdated)
		if (PACKAGE_ID && PACKAGE_ID != 1) {
			// reset object type cache
			ObjectTypeCache::getInstance()->resetCache();
			
			// get object type
			$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.dashboardContainer', $objectType);
			if ($objectTypeObj === null) {
				throw new SystemException("Object type '".$objectType."' is not valid for definition 'com.woltlab.wcf.user.dashboardContainer'");
			}
			
			$objectTypeID = $objectTypeObj->objectTypeID;
			
			// select available box ids
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("boxName IN (?)", array(array_keys($enableBoxNames)));
			
			$sql = "SELECT	boxID, boxName, boxType
				FROM	wcf".WCF_N."_dashboard_box
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
		}
		else {
			// work-around during WCFSetup
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("object_type.objectType = ?", array($objectType));
			$conditions->add("object_type_definition.definitionName = ?", array('com.woltlab.wcf.user.dashboardContainer'));
			
			$sql = "SELECT		object_type.objectTypeID
				FROM		wcf".WCF_N."_object_type object_type
				LEFT JOIN	wcf".WCF_N."_object_type_definition object_type_definition
				ON		(object_type_definition.definitionID = object_type.definitionID)
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$row = $statement->fetchArray();
			if ($row) {
				$objectTypeID = $row['objectTypeID'];
			}
			
			if (!$objectTypeID) {
				throw new SystemException("Object type '".$objectType."' is not valid for definition 'com.woltlab.wcf.user.dashboardContainer'");
			}
			
			// select available box ids
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("boxName IN (?)", array(array_keys($enableBoxNames)));
			
			$sql = "SELECT	boxID, boxName, boxType
				FROM	wcf".WCF_N."_dashboard_box
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
		}
		
		$boxes = array();
		while ($row = $statement->fetchArray()) {
			$boxes[$row['boxID']] = new DashboardBox(null, $row);
		}
		
		if (!empty($boxes)) {
			$sql = "UPDATE		wcf".WCF_N."_dashboard_option
				SET		showOrder = showOrder + 1
				WHERE		objectTypeID = ?
						AND boxID IN (SELECT boxID FROM wcf".WCF_N."_dashboard_box WHERE boxType = ?)
						AND showOrder >= ?";
			$updateStatement = WCF::getDB()->prepareStatement($sql);
			$sql = "INSERT INTO	wcf".WCF_N."_dashboard_option
						(objectTypeID, boxID, showOrder)
				VALUES		(?, ?, ?)";
			$insertStatement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($boxes as $boxID => $box) {
				// move other boxes
				$updateStatement->execute(array(
					$objectTypeID,
					$box->boxType,
					$enableBoxNames[$box->boxName]
				));
				
				// insert associations
				$insertStatement->execute(array(
					$objectTypeID,
					$boxID,
					$enableBoxNames[$box->boxName]
				));
			}
			WCF::getDB()->commitTransaction();
		}
	}
	
	/**
	 * Clears dashboard box cache.
	 */
	public static function clearCache() {
		DashboardBoxCacheBuilder::getInstance()->reset();
	}
}
