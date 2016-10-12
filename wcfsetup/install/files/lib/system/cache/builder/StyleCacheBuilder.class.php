<?php
namespace wcf\system\cache\builder;
use wcf\data\style\Style;
use wcf\system\WCF;

/**
 * Caches the styles and style variables.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class StyleCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = [
			'default' => 0,
			'styles' => []
		];
		
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
