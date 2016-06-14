<?php
namespace wcf\system\cache\builder;
use wcf\data\smiley\Smiley;
use wcf\system\WCF;

/**
 * Caches the smilies.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class SmileyCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = ['smilies' => []];
		
		// get smilies
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_smiley
			ORDER BY	showOrder";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		/** @var Smiley $object */
		while ($object = $statement->fetchObject(Smiley::class)) {
			$object->smileyCodes = $object->getAliases();
			$object->smileyCodes[] = $object->smileyCode;
			
			$data['smilies'][$object->categoryID][$object->smileyID] = $object;
		}
		
		return $data;
	}
}
