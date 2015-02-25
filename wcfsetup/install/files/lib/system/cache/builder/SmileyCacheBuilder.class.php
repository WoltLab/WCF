<?php
namespace wcf\system\cache\builder;
use wcf\system\WCF;

/**
 * Caches the smilies.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class SmileyCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$data = array('smilies' => array());
		
		// get smilies
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_smiley
			ORDER BY	showOrder";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($object = $statement->fetchObject('wcf\data\smiley\Smiley')) {
			$object->smileyCodes = $object->getAliases();
			$object->smileyCodes[] = $object->smileyCode;
			
			$data['smilies'][$object->categoryID][$object->smileyID] = $object;
		}
		
		return $data;
	}
}
