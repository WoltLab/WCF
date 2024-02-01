<?php

namespace wcf\system\html\metacode\upcast;

use wcf\data\media\Media;
use wcf\system\cache\runtime\MediaRuntimeCache;
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
        /**
         * @var string $alignment
         * @var string|int $width
         * @var string $thumbnail
         */
        $mediaID = \intval($attributes[0]);
        $thumbnail = $attributes[1] ?? 'original';
        $alignment = $attributes[2] ?? 'none';
        $width = $attributes[3] ?? 'auto';
        $media = MediaRuntimeCache::getInstance()->getObject($mediaID);

        $element = $fragment->ownerDocument->createElement('img');
        if ($thumbnail === 'original') {
            $element->setAttribute('src', StringUtil::decodeHTML($media->getLink()));
        } else {
            $element->setAttribute('src', StringUtil::decodeHTML($media->getThumbnailLink($thumbnail)));
        }
        if ($width !== 'auto') {
            $element->setAttribute('width', \intval($width));
            $element->setAttribute('data-width', \intval($width) . 'px');
        }
        $element->setAttribute('data-media-id', $mediaID);
        $element->setAttribute('data-media-size', StringUtil::decodeHTML($thumbnail));
        if ($alignment === 'none') {
            $element->setAttribute('class', 'image woltlabSuiteMedia');
            $element->setAttribute('style', $this->getStyle($media, $width, $thumbnail));
            return $element;
        }
        $element->setAttribute('class', 'woltlabSuiteMedia');

        $figure = $fragment->ownerDocument->createElement('figure');
        if ($alignment === 'left') {
            $figure->setAttribute('class', 'image image-style-side-left');
        } elseif ($alignment === 'right') {
            $figure->setAttribute('class', 'image image-style-side');
        } else {
            $figure->setAttribute('class', 'image');
        }
        $figure->setAttribute('style', $this->getStyle($media, $width, $thumbnail));
        $figure->appendChild($element);
        return $figure;
    }

    #[\Override]
    public function hasValidAttributes(array $attributes): bool
    {
        // 1-4 attributes
        if (\count($attributes) < 1 || \count($attributes) > 4) {
            return false;
        }
        $media = MediaRuntimeCache::getInstance()->getObject($attributes[0]);
        if ($media === null) {
            return false;
        }
        if (!$media->isAccessible()) {
            return false;
        }
        // Other media types must be converted to the text [wsm…][/wsm]
        return (bool)$media->isImage;
    }

    #[\Override]
    public function cacheObject(array $attributes): void
    {
        MediaRuntimeCache::getInstance()->cacheObjectID($attributes[0] ?? 0);
    }

    private function getStyle(Media $media, string|int $width, string $thumbnail): string
    {
        if ($thumbnail === 'original') {
            $maxWidth = $media->width;
        } else {
            $maxWidth = $media->getThumbnailWidth($thumbnail);
        }
        return \sprintf(
            'max-width: %dpx; width: %s;',
            $maxWidth,
            \is_numeric($width) && $width > 0 ? \intval($width) . 'px' : 'auto'
        );
    }
}
