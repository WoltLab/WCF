<?php
namespace wcf\system\cache\builder;
use wcf\data\style\Style;
use wcf\system\WCF;

/**
 * Caches the styles and style variables.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class StyleCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$data = array(
			'default' => 0,
			'styles' => array()
		);
		
		// get all styles
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_style
			ORDER BY	styleName ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if ($row['isDefault']) $data['default'] = $row['styleID'];
			$style = new Style(null, $row);
			$style->loadVariables();
			
			$data['styles'][$row['styleID']] = $style;
		}
		
		return $data;
	}
}
