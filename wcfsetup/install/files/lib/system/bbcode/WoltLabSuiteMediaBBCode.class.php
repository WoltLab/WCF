<?php
namespace wcf\system\bbcode;
use wcf\data\media\ViewableMedia;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [wsm] bbcode tag.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 * @since       3.0
 */
class WoltLabSuiteMediaBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$mediaID = (!empty($openingTag['attributes'][0])) ? intval($openingTag['attributes'][0]) : 0;
		if (!$mediaID) {
			return '';
		}
		
		/** @var ViewableMedia $media */
		$media = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.media', $mediaID);
		if ($media !== null && $media->isAccessible()) {
			if ($media->isImage) {
				$thumbnailSize = (!empty($openingTag['attributes'][1])) ? $openingTag['attributes'][1] : 'original';
				$float = (!empty($openingTag['attributes'][2])) ? $openingTag['attributes'][2] : 'none';
				
				WCF::getTPL()->assign([
					'float' => $float,
					'media' => $media->getLocalizedVersion(MessageEmbeddedObjectManager::getInstance()->getActiveMessageLanguageID()),
					'thumbnailSize' => $thumbnailSize
				]);
				
				return WCF::getTPL()->fetch('mediaBBCodeTag', 'wcf');
			}
			
			return StringUtil::getAnchorTag($media->getLink(), $media->getTitle());
		}
		
		return '';
	}
}
