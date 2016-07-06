<?php
namespace wcf\system\message\embedded\object;
use wcf\system\cache\runtime\MediaRuntimeCache;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\util\ArrayUtil;

/**
 * IMessageEmbeddedObjectHandler implementation for shared media.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message\Embedded\Object
 */
class MediaMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
		$mediaIDs = [];
		foreach (['wsm', 'wsmg'] as $name) {
			if (empty($embeddedData[$name])) {
				continue;
			}
			
			for ($i = 0, $length = count($embeddedData[$name]); $i < $length; $i++) {
				$parsedIDs = ArrayUtil::toIntegerArray(explode(',', $embeddedData[$name][$i][0]));
				
				$mediaIDs = array_merge($mediaIDs, $parsedIDs);
			}
		}
		
		return $mediaIDs;
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadObjects(array $objectIDs) {
		return MediaRuntimeCache::getInstance()->getObjects($objectIDs);
	}
}
