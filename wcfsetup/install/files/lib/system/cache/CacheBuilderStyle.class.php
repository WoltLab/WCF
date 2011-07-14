<?php
namespace wcf\system\cache;
use wcf\data\style\Style;
use wcf\system\WCF;

/**
 * Caches the styles and style variables.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderStyle implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		$data = array('default' => 0, 'styles' => array(), 'packages' => array());
		
		// get all styles
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_style
			ORDER BY	styleName ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if ($row['isDefault']) $data['default'] = $row['styleID'];
			$row['variables'] = array();
			
			// get variable
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_style_variable
				WHERE	styleID = ?";
			$statement2 = WCF::getDB()->prepareStatement($sql);
			$statement2->execute(array($row['styleID']));
			while ($row = $statement2->fetchArray()) {
				
				$row['variables'][$row2['variableName']] = $row2['variableValue'];
			}
			
			$data['styles'][$row['styleID']] = new Style(null, $row);
		}
		
		// get style to packages
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_style_to_package
			ORDER BY	packageID ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			
			if (!isset($data['packages'][$row['packageID']])) {
				$data['packages'][$row['packageID']] = array('default' => 0, 'disabled' => array());
			}
			
			if ($row['isDefault']) {
				$data['packages'][$row['packageID']]['default'] = $row['styleID'];
			}
			$data['packages'][$row['packageID']]['disabled'][$row['styleID']] = $row['disabled'];
		}
		
		return $data;
	}
}
