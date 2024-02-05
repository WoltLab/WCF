<?php

namespace wcf\system\html\metacode\upcast;

use wcf\data\attachment\Attachment;
use wcf\system\cache\runtime\AttachmentRuntimeCache;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class AttachMetacodeUpcast extends ImageMetacodeUpcast
{
    #[\Override]
    public function upcast(\DOMElement $element, array $attributes): void
    {
        /**
         * @var string $alignment
         * @var string|bool|int $width
         */
        $attachmentID = \intval($attributes[0]);
        $alignment = $attributes[1] ?? 'none';
        $width = $attributes[2] ?? 'auto';
        $attachment = AttachmentRuntimeCache::getInstance()->getObject($attachmentID);
        $parentLink = $element->parentNode;
        /** @var \DOMElement|null $parentLink */
        if ($parentLink !== null && $parentLink->nodeName !== 'a') {
            $parentLink = null;
        }

        $imgElement = $element->ownerDocument->createElement('img');
        if ($this->isThumbnailWidth($attachment, $width)) {
            $imgElement->setAttribute('src', StringUtil::decodeHTML($attachment->getThumbnailLink('thumbnail')));
        } else {
            $imgElement->setAttribute('src', StringUtil::decodeHTML($attachment->getLink()));
        }

        if (\is_numeric($width) && $width > 0) {
            $imgElement->setAttribute('width', \intval($width));
            $imgElement->setAttribute('data-width', \intval($width) . 'px');
        }
        $imgElement->setAttribute('data-attachment-id', $attachmentID);
        $imgElement->setAttribute('style', $this->getStyle($attachment, $width));
        if ($alignment === 'none') {
            $imgElement->setAttribute('class', 'image woltlabAttachment');
            DOMUtil::replaceElement($element, $imgElement);
            return;
        }
        $imgElement->setAttribute('class', 'woltlabAttachment');

        $this->createFigure($element, $imgElement, $alignment, $parentLink);
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
