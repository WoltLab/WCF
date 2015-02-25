<?php
namespace wcf\system\message;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Provides utility functions for common tasks related to inline editing and quick reply.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message
 * @category	Community Framework
 */
class MessageFormSettingsHandler {
	/**
	 * Computes the settings for BBCodes, Smilies and pre parsing. Optionally accepts the corresponding DatabaseObject
	 * whose values will be used in case the settings did not contain the individual values (legacy support).
	 * 
	 * @param	array<array>			$parameters
	 * @param	\wcf\data\DatabaseObject	$object
	 * @param	string				$permissionCanUseBBCodes
	 * @param	string				$permissionCanUseSmilies
	 * @return	array
	 */
	public static function getSettings(array $parameters, DatabaseObject $object = null, $permissionCanUseBBCodes = '', $permissionCanUseSmilies = '') {
		$permissionCanUseBBCodes = ($permissionCanUseBBCodes) ?: 'user.message.canUseBBCodes';
		$permissionCanUseSmilies = ($permissionCanUseSmilies) ?: 'user.message.canUseSmilies';
		
		$enableSmilies = 0;
		$enableBBCodes = 0;
		$preParse = 0;
		
		if (WCF::getSession()->getPermission($permissionCanUseSmilies)) {
			if (isset($parameters['enableSmilies'])) {
				$enableSmilies = ($parameters['enableSmilies']) ? 1 : 0;
			}
			else {
				$enableSmilies = ($object === null) ? 1 : $object->enableSmilies;
			}
		}
		else if ($object !== null) {
			$enableSmilies = ($object->enableSmilies) ? 1 : 0;
		}
		
		if (WCF::getSession()->getPermission($permissionCanUseBBCodes)) {
			if (isset($parameters['enableBBCodes'])) {
				$enableBBCodes = ($parameters['enableBBCodes']) ? 1 : 0;
			}
			else {
				$enableBBCodes = ($object === null) ? 1 : $object->enableBBCodes;
			}
			
			if (isset($parameters['preParse'])) {
				$preParse = ($parameters['preParse'] && $enableBBCodes) ? 1 : 0;
			}
			else {
				$preParse = $enableBBCodes;
			}
		}
		else if ($object !== null) {
			$enableBBCodes = $preParse = ($object->enableBBCodes) ? 1 : 0;
		}
		
		return array(
			'enableSmilies' => $enableSmilies,
			'enableBBCodes' => $enableBBCodes,
			'preParse' => $preParse
		);
	}
}
