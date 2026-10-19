<?php

namespace wcf\system\bbcode;

use wcf\data\media\Media;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\view\ContentNotVisibleView;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [wsmg] bbcode tag that embeds multiple images as a gallery.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class WoltLabSuiteMediaGalleryBBCode extends AbstractBBCode
{
    #[\Override]
    public function getParsedTag(array $openingTag, string $content, array $closingTag, BBCodeParser $parser): string
    {
        $mediaIDs = $this->getMediaIDs($openingTag['attributes'][0] ?? '');
        if ($mediaIDs === []) {
            return '';
        }

        $mediaList = [];
        $hasInaccessibleMedia = false;
        foreach ($mediaIDs as $mediaID) {
            /** @var ?Media $media */
            $media = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.media', $mediaID);
            if ($media === null || $media->isImage === 0) {
                continue;
            }

            if (!$media->isAccessible()) {
                $hasInaccessibleMedia = true;
                continue;
            }

            $mediaList[] = $media;
        }

        if ($mediaList === []) {
            return $hasInaccessibleMedia
                ? ContentNotVisibleView::forNoPermission()
                : ContentNotVisibleView::forNotAvailable();
        }

        if ($parser->getOutputType() === 'text/html') {
            $removeLinks = false;
            /** @var ?\DOMElement $element */
            $element = $closingTag['__parents'][0] ?? null;
            if ($element !== null && $element->nodeName === 'a') {
                // The gallery may be nested inside a link, but the images must not be linked.
                $removeLinks = true;
            }

            $languageID = MessageEmbeddedObjectManager::getInstance()->getActiveMessageLanguageID();
            if ($languageID !== null) {
                $mediaList = \array_map(
                    static fn(Media $media) => $media->getLocalizedVersion($languageID),
                    $mediaList
                );
            }

            return WCF::getTPL()->render('wcf', 'shared_bbcode_wsmg', [
                'mediaList' => $mediaList,
                'removeLinks' => $removeLinks,
                'activeMessageID' => MessageEmbeddedObjectManager::getInstance()->getActiveMessageID(),
                'activeMessageObjectType' => MessageEmbeddedObjectManager::getInstance()->getActiveMessageObjectType(),
            ]);
        } elseif ($parser->getOutputType() === 'text/simplified-html') {
            return \implode('<br>', \array_map(
                static fn(Media $media) => StringUtil::getAnchorTag($media->getLink(), $media->getTitle()),
                $mediaList
            ));
        }

        return \implode("\n", \array_map(
            static fn(Media $media) => StringUtil::encodeHTML($media->getLink()),
            $mediaList
        ));
    }

    /**
     * @return list<int>
     */
    private function getMediaIDs(string $value): array
    {
        $mediaIDs = [];
        foreach (\explode(',', $value) as $mediaID) {
            $mediaID = (int)$mediaID;
            if ($mediaID > 0 && !\in_array($mediaID, $mediaIDs, true)) {
                $mediaIDs[] = $mediaID;
            }
        }

        return $mediaIDs;
    }
}
