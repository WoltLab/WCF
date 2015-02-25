<?php
namespace wcf\system\cache\builder;
use wcf\data\dashboard\box\DashboardBoxList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches user dashboard boxes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class DashboardBoxCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$data = array(
			'boxes' => array(),
			'pages' => array()
		);
		
		// load boxes
		$boxList = new DashboardBoxList();
		$boxList->readObjects();
		
		foreach ($boxList as $box) {
			$data['boxes'][$box->boxID] = $box;
		}
		
		// load settings
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.dashboardContainer');
		$objectTypeIDs = array();
		foreach ($objectTypes as $objectType) {
			$objectTypeIDs[] = $objectType->objectTypeID;
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("objectTypeID IN (?)", array($objectTypeIDs));
		
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_dashboard_option
			".$conditions."
			ORDER BY	showOrder ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			if (!isset($data['pages'][$row['objectTypeID']])) {
				$data['pages'][$row['objectTypeID']] = array();
			}
			
			$data['pages'][$row['objectTypeID']][] = $row['boxID'];
		}
		
		return $data;
	}
}
