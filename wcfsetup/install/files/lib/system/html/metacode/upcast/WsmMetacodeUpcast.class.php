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

        $img = $fragment->ownerDocument->createElement('img');
        if ($thumbnail === 'original') {
            $img->setAttribute('src', StringUtil::decodeHTML($media->getLink()));
        } else {
            $img->setAttribute('src', StringUtil::decodeHTML($media->getThumbnailLink($thumbnail)));
        }
        if ($width !== 'auto') {
            $img->setAttribute('width', \intval($width));
        }
        if ($alignment === 'none') {
            return $img;
        }
        $figure = $fragment->ownerDocument->createElement('figure');
        $figure->appendChild($img);
        if ($alignment === 'left') {
            $figure->setAttribute('class', 'image image-style-side-left');
        } elseif ($alignment === 'right') {
            $figure->setAttribute('class', 'image image-style-side');
        }
        return $figure;
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
