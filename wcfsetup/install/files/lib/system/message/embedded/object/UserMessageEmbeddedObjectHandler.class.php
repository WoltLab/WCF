<?php
namespace wcf\system\message\embedded\object;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Parses embedded users.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message\Embedded\Object
 */
class UserMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
		$objectIDs = [];
		if (!empty($embeddedData['user'])) {
			for ($i = 0, $length = count($embeddedData['user']); $i < $length; $i++) {
				$objectIDs[] = intval($embeddedData['user'][$i][0]);
			}
		}
		
		return array_unique($objectIDs);
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadObjects(array $objectIDs) {
		return UserProfileRuntimeCache::getInstance()->getObjects($objectIDs);
	}
}
