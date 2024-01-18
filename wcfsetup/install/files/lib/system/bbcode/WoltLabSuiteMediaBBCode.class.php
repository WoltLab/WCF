<?php

namespace wcf\system\bbcode;

use wcf\data\media\ViewableMedia;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [wsm] bbcode tag.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
final class WoltLabSuiteMediaBBCode extends AbstractBBCode
{
    /**
     * forces media links to be linked to the frontend
     * after it has been set to `true`, it should be set to `false` again as soon as possible
     * @var bool
     */
    public static $forceFrontendLinks = false;

    /**
     * @inheritDoc
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser): string
    {
        $mediaID = (!empty($openingTag['attributes'][0])) ? \intval($openingTag['attributes'][0]) : 0;
        if (!$mediaID) {
            return '';
        }

        $removeLinks = false;
        /** @var \DOMElement $element */
        $element = $closingTag['__parents'][0] ?? null;
        if ($element && $element->nodeName === 'a') {
            // We do permit media elements to be nested inside a link, but we must suppress
            // the thumbnail link to be generated. Removing the link technically is meant
            // to be something else, but we use it here for backward compatibility.
            $removeLinks = true;
        }

        /** @var ViewableMedia $media */
        $media = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.media', $mediaID);
        if ($media === null) {
            return WCF::getTPL()->fetch('contentNotVisible');
        }

        if ($media->isAccessible()) {
            if ($removeLinks && !$media->isImage) {
                if ($parser->getOutputType() === 'text/html' || $parser->getOutputType() === 'text/simplified-html') {
                    return StringUtil::encodeHTML($media->getTitle());
                }

                return StringUtil::encodeHTML($this->getLink($media));
            }

            if ($parser->getOutputType() == 'text/html') {
                if ($media->isImage) {
                    $thumbnailSize = (!empty($openingTag['attributes'][1])) ? $openingTag['attributes'][1] : 'original';
                    $float = (!empty($openingTag['attributes'][2])) ? $openingTag['attributes'][2] : 'none';
                    $width = (!empty($openingTag['attributes'][3])) ? $openingTag['attributes'][3] : 'auto';

                    return WCF::getTPL()->fetch('mediaBBCodeTag', 'wcf', [
                        'mediaLink' => $this->getLink($media),
                        'removeLinks' => $removeLinks,
                        'thumbnailLink' => $thumbnailSize !== 'original' ? $this->getThumbnailLink(
                            $media,
                            $thumbnailSize
                        ) : '',
                        'float' => $float,
                        'media' => $media->getLocalizedVersion(MessageEmbeddedObjectManager::getInstance()->getActiveMessageLanguageID()),
                        'thumbnailSize' => $thumbnailSize,
                        'width' => $width,
                    ]);
                } elseif ($media->isVideo() || $media->isAudio()) {
                    return WCF::getTPL()->fetch('mediaBBCodeTag', 'wcf', [
                        'mediaLink' => $this->getLink($media),
                        'removeLinks' => $removeLinks,
                        'float' => 'none',
                        'media' => $media->getLocalizedVersion(MessageEmbeddedObjectManager::getInstance()->getActiveMessageLanguageID()),
                        'width' => 'auto',
                    ]);
                }

                return StringUtil::getAnchorTag($this->getLink($media), $media->getTitle());
            } elseif ($parser->getOutputType() == 'text/simplified-html') {
                return StringUtil::getAnchorTag($this->getLink($media), $media->getTitle());
            }

            return StringUtil::encodeHTML($this->getLink($media));
        } else {
            return WCF::getTPL()->fetch('contentNotVisible', 'wcf', [
                'message' => WCF::getLanguage()->get('wcf.message.content.no.permission.title')
            ], true);
        }
    }

    /**
     * Returns the link to the given media file (while considering the value of `$forceFrontendLinks`).
     *
     * @param ViewableMedia $media linked media file
     * @return  string              link to media file
     */
    protected function getLink(ViewableMedia $media)
    {
        if (self::$forceFrontendLinks) {
            return LinkHandler::getInstance()->getLink('Media', [
                'forceFrontend' => 'true',
                'object' => $media,
            ]);
        }

        return $media->getLink();
    }

    /**
     * Returns the thumbnail link to the given media file (while considering the value of `$forceFrontendLinks`).
     *
     * @param ViewableMedia $media linked media file
     * @param string $thumbnailSize thumbnail size
     * @return  string              link to media thumbnail
     */
    protected function getThumbnailLink(ViewableMedia $media, $thumbnailSize)
    {
        // use `Media::getThumbnailLink()` to validate thumbnail size
        $thumbnailLink = $media->getThumbnailLink($thumbnailSize);

        if (self::$forceFrontendLinks) {
            if (!$this->{$thumbnailSize . 'ThumbnailType'}) {
                return $this->getLink($media);
            }

            return LinkHandler::getInstance()->getLink('Media', [
                'forceFrontend' => 'true',
                'object' => $media,
                'thumbnail' => $thumbnailSize,
            ]);
        }

        return $thumbnailLink;
    }
}
