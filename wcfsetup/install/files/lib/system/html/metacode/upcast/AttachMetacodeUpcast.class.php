<?php

namespace wcf\system\html\metacode\upcast;

use wcf\data\attachment\Attachment;
use wcf\system\cache\runtime\AttachmentRuntimeCache;
use wcf\util\StringUtil;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class AttachMetacodeUpcast implements IMetacodeUpcast
{
    #[\Override]
    public function upcast(\DOMDocumentFragment $fragment, array $attributes): ?\DOMElement
    {
        /**
         * @var string $alignment
         * @var string|bool|int $width
         */
        $attachmentID = \intval($attributes[0]);
        $alignment = $attributes[1] ?? 'none';
        $width = $attributes[2] ?? 'auto';
        $attachment = AttachmentRuntimeCache::getInstance()->getObject($attachmentID);

        $element = $fragment->ownerDocument->createElement('img');
        if ($this->isThumbnailWidth($attachment, $width)) {
            $element->setAttribute('src', StringUtil::decodeHTML($attachment->getThumbnailLink('thumbnail')));
        } else {
            $element->setAttribute('src', StringUtil::decodeHTML($attachment->getLink()));
        }

        if (\is_numeric($width) && $width > 0) {
            $element->setAttribute('width', \intval($width));
            $element->setAttribute('data-width', \intval($width) . 'px');
        }
        $element->setAttribute('data-attachment-id', $attachmentID);
        if ($alignment === 'none') {
            $element->setAttribute('class', 'image woltlabAttachment');
            $element->setAttribute(
                'style',
                $this->getStyle($attachment, $width)
            );
            return $element;
        }
        $element->setAttribute('class', 'woltlabAttachment');

        $figure = $fragment->ownerDocument->createElement('figure');
        if ($alignment === 'left') {
            $figure->setAttribute('class', 'image image-style-side-left');
        } elseif ($alignment === 'right') {
            $figure->setAttribute('class', 'image image-style-side');
        } else {
            $figure->setAttribute('class', 'image');
        }
        if ($width !== 'auto' && \is_numeric($width) && $width > 0) {
            $figure->setAttribute(
                'style',
                $this->getStyle($attachment, $width)
            );
        }
        $figure->appendChild($element);
        return $figure;
    }

    private function isThumbnailWidth(Attachment $attachment, string|bool|int $width): bool
    {
        if ($width === 'auto' || $width === false) {
            return false;
        }
        if ($width === true || $width === 0) {
            return true;
        }
        return !($width > $attachment->thumbnailWidth);
    }

    private function getStyle(Attachment $attachment, string|bool|int $width): string
    {
        return \sprintf(
            'max-width: %dpx; width: %s;',
            $this->getMaxWidth($attachment, $width),
            \is_numeric($width) && $width > 0 ? \intval($width) . 'px' : 'auto'
        );
    }

    private function getMaxWidth(Attachment $attachment, string|bool|int $width): int
    {
        return $this->isThumbnailWidth($attachment, $width) ? $attachment->thumbnailWidth : $attachment->width;
    }

    #[\Override]
    public function hasValidAttributes(array $attributes): bool
    {
        // 1-3 attributes
        if (\count($attributes) < 1 || \count($attributes) > 3) {
            return false;
        }
        $attachment = AttachmentRuntimeCache::getInstance()->getObject($attributes[0]);
        if ($attachment === null) {
            return false;
        }
        if (!$attachment->canDownload()) {
            return false;
        }
        return (bool)$attachment->isImage;
    }

    #[\Override]
    public function cacheObject(array $attributes): void
    {
        AttachmentRuntimeCache::getInstance()->cacheObjectID($attributes[0] ?? 0);
    }
}
