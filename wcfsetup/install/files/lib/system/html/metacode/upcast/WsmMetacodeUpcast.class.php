<?php

namespace wcf\system\html\metacode\upcast;

use wcf\system\cache\runtime\ViewableMediaRuntimeCache;
use wcf\util\StringUtil;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class WsmMetacodeUpcast implements IMetacodeUpcast
{
    #[\Override]
    public function upcast(\DOMDocumentFragment $fragment, array $attributes): ?\DOMElement
    {
        $mediaID = $attributes[0];
        $thumbnail = $attributes[1] ?? 'original';
        $alignment = $attributes[2] ?? 'none';
        $width = $attributes[3] ?? 'auto';
        $media = ViewableMediaRuntimeCache::getInstance()->getObject($mediaID);

        $element = $fragment->ownerDocument->createElement('img');
        if ($thumbnail === 'original') {
            $element->setAttribute('src', StringUtil::decodeHTML($media->getLink()));
        } else {
            $element->setAttribute('src', StringUtil::decodeHTML($media->getThumbnailLink($thumbnail)));
        }
        if ($width !== 'auto') {
            $element->setAttribute('width', \intval($width));
        }
        if ($alignment === 'none') {
            $element->setAttribute('class', 'image woltlabSuiteMedia');
        } else {
            $figure = $fragment->ownerDocument->createElement('figure');
            if ($alignment === 'left') {
                $figure->setAttribute('class', 'image woltlabSuiteMedia image-style-side-left');
            } elseif ($alignment === 'right') {
                $figure->setAttribute('class', 'image woltlabSuiteMedia image-style-side');
            } else {
                $figure->setAttribute('class', 'image woltlabSuiteMedia');
            }
            $figure->appendChild($element);
            $element = $figure;
        }
        $element->setAttribute('data-media-id', \intval($mediaID));
        $element->setAttribute('data-media-size', StringUtil::decodeHTML($thumbnail));
        return $element;
    }

    #[\Override]
    public function hasValidAttributes(array $attributes): bool
    {
        // 1-4 attributes
        if (\count($attributes) < 1 || \count($attributes) > 4) {
            return false;
        }
        $mediaID = $attributes[0];
        $media = ViewableMediaRuntimeCache::getInstance()->getObject($mediaID);
        if ($media === null) {
            return false;
        }
        if (!$media->isAccessible()) {
            return false;
        }
        // Other media types must be converted to the text [wsm…][/wsm]
        return (bool)$media->isImage;
    }
}
