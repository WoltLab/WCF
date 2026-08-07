<?php

namespace wcf\system\bbcode;

use wcf\data\media\ViewableMedia;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\view\ContentNotVisibleView;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [wsm] bbcode tag.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class WoltLabSuiteMediaBBCode extends AbstractBBCode
{
    /**
     * forces media links to be linked to the frontend
     * after it has been set to `true`, it should be set to `false` again as soon as possible
     * @deprecated 6.1 media links are always linked to the frontend
     */
    public static bool $forceFrontendLinks = false;

    #[\Override]
    public function getParsedTag(array $openingTag, string $content, array $closingTag, BBCodeParser $parser): string
    {
        $mediaID = (!empty($openingTag['attributes'][0])) ? \intval($openingTag['attributes'][0]) : 0;
        if (!$mediaID) {
            return '';
        }

        $removeLinks = false;
        /** @var ?\DOMElement $element */
        $element = $closingTag['__parents'][0] ?? null;
        if ($element && $element->nodeName === 'a') {
            // We do permit media elements to be nested inside a link, but we must suppress
            // the thumbnail link to be generated. Removing the link technically is meant
            // to be something else, but we use it here for backward compatibility.
            $removeLinks = true;
        }

        /** @var ?ViewableMedia $media */
        $media = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.media', $mediaID);
        if ($media === null) {
            return ContentNotVisibleView::forNotAvailable();
        }

        if ($media->isAccessible()) {
            if ($removeLinks && !$media->isImage) {
                if ($parser->getOutputType() === 'text/html' || $parser->getOutputType() === 'text/simplified-html') {
                    return StringUtil::encodeHTML($media->getTitle());
                }

                return StringUtil::encodeHTML($media->getLink());
            }

            if ($parser->getOutputType() === 'text/html') {
                $float = (!empty($openingTag['attributes'][2])) ? $openingTag['attributes'][2] : 'none';
                $localizedMedia = MessageEmbeddedObjectManager::getInstance()->getActiveMessageLanguageID()
                    ? $media->getLocalizedVersion(MessageEmbeddedObjectManager::getInstance()->getActiveMessageLanguageID())
                    : $media;

                if ($media->isImage) {
                    $thumbnailSize = (!empty($openingTag['attributes'][1])) ? $openingTag['attributes'][1] : 'original';
                    $width = (!empty($openingTag['attributes'][3])) ? $openingTag['attributes'][3] : 'auto';

                    return WCF::getTPL()->render('wcf', 'shared_bbcode_wsm', [
                        'mediaLink' => $media->getLink(),
                        'removeLinks' => $removeLinks,
                        'thumbnailLink' => $thumbnailSize !== 'original' ? $media->getThumbnailLink(
                            $thumbnailSize
                        ) : '',
                        'float' => $float,
                        'media' => $localizedMedia,
                        'thumbnailSize' => $thumbnailSize,
                        'width' => $width,
                        'activeMessageID' => MessageEmbeddedObjectManager::getInstance()->getActiveMessageID(),
                        'activeMessageObjectType' => MessageEmbeddedObjectManager::getInstance()->getActiveMessageObjectType(),
                    ]);
                } elseif ($media->isVideo() || $media->isAudio()) {
                    return WCF::getTPL()->render('wcf', 'shared_bbcode_wsm', [
                        'mediaLink' => $media->getLink(),
                        'removeLinks' => $removeLinks,
                        'float' => $float,
                        'media' => $localizedMedia,
                        'width' => 'auto',
                    ]);
                } else {
                    return WCF::getTPL()->render('wcf', 'shared_bbcode_wsm_file', [
                        'media' => $localizedMedia,
                    ]);
                }
            } elseif ($parser->getOutputType() === 'text/simplified-html') {
                return StringUtil::getAnchorTag($media->getLink(), $media->getTitle());
            }

            return StringUtil::encodeHTML($media->getLink());
        } else {
            return ContentNotVisibleView::forNoPermission();
        }
    }
}
