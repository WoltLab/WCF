<?php

namespace wcf\system\bbcode;

use wcf\data\attachment\Attachment;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [attach] bbcode tag.
 *
 * @author Alexander Ebert, Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class AttachmentBBCode extends AbstractBBCode
{
    /**
     * @inheritDoc
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser): string
    {
        $attachmentID = \intval($openingTag['attributes'][0] ?? 0);

        $attachment = $this->getAttachment($attachmentID);
        if ($attachment === null) {
            return StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Attachment', [
                'id' => $attachmentID,
            ]));
        }

        $outputType = $parser->getOutputType();

        if ($attachment->showAsImage() && $attachment->canViewPreview() && ($outputType == 'text/html' || $outputType == 'text/simplified-html')) {
            return $this->showImage($attachment, $outputType, $openingTag['attributes']);
        } elseif (\substr($attachment->fileType, 0, 6) === 'video/' && $outputType == 'text/html') {
            return $this->showVideoPlayer($attachment);
        } elseif (\substr($attachment->fileType, 0, 6) === 'audio/' && $outputType == 'text/html') {
            return $this->showAudioPlayer($attachment);
        }

        return StringUtil::getAnchorTag($attachment->getLink(), $attachment->filename);
    }

    private function showImage(Attachment $attachment, string $outputType, array $attributes): string
    {
        $alignment = $attributes[1] ?? '';

        $thumbnail = $this->renderImageAsThumbnail($attachment, $outputType, $attributes[2] ?? false);

        $hasParentLink = false;
        if (!empty($closingTag['__parents'])) {
            /** @var \DOMElement $parent */
            foreach ($closingTag['__parents'] as $parent) {
                if ($parent->nodeName === 'a') {
                    $hasParentLink = true;
                    break;
                }
            }
        }

        if ($thumbnail === false) {
            $class = '';
            if ($alignment === 'left' || $alignment === 'right') {
                $class = 'messageFloatObject' . \ucfirst($alignment);
            }

            $source = StringUtil::encodeHTML($attachment->getLink());
            $title = StringUtil::encodeHTML($attachment->filename);
            $image = \sprintf(
                '<img src="%s" width="%d" height="%d" alt="" loading="lazy">',
                $source,
                $attachment->width,
                $attachment->height,
            );

            if (!$hasParentLink && ($attachment->width > ATTACHMENT_THUMBNAIL_WIDTH || $attachment->height > ATTACHMENT_THUMBNAIL_HEIGHT)) {
                $icon = FontAwesomeIcon::fromValues('magnifying-glass')->toHtml(24);
                return <<<HTML
                    <a href="{$source}" title="{$title}" class="embeddedAttachmentLink jsImageViewer {$class}'">
                        {$image}
                        <span class="embeddedAttachmentLinkEnlarge">
                            {$icon}
                        </span>
                    </a>
                    HTML;
            }

            return \sprintf(
                '<span title="%s" class="%s">%s</span>',
                $title,
                $class,
                $image,
            );
        }

        $icon = FontAwesomeIcon::fromValues('magnifying-glass')->toHtml(24);
        $enlarge = '<span class="embeddedAttachmentLinkEnlarge">' . $icon . '</span>';

        $linkParameters = [
            'object' => $attachment,
        ];
        if ($attachment->hasThumbnail()) {
            $linkParameters['thumbnail'] = 1;
        }

        $class = '';
        if ($alignment == 'left' || $alignment == 'right') {
            $class = 'messageFloatObject' . \ucfirst($alignment);
        }

        $imageClasses = '';
        if (!$attachment->hasThumbnail()) {
            $imageClasses = 'embeddedAttachmentLink jsResizeImage';
        }

        if ($class && (!$attachment->hasThumbnail() || !$attachment->canDownload())) {
            $imageClasses .= ' ' . $class;
        }

        $image = \sprintf(
            '<img src="%s" class="%s" width="%d" height="%d" alt="" loading="lazy">',
            StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('Attachment', $linkParameters)),
            $imageClasses,
            $attachment->hasThumbnail() ? $attachment->thumbnailWidth : $attachment->width,
            $attachment->hasThumbnail() ? $attachment->thumbnailHeight : $attachment->height,
        );

        if (!$hasParentLink && $attachment->hasThumbnail() && $attachment->canDownload()) {
            return \sprintf(
                '<a href="%s" title="%s" class="embeddedAttachmentLink jsImageViewer %s">%s%s</a>',
                StringUtil::encodeHTML(LinkHandler::getInstance()->getLink('Attachment', ['object' => $attachment])),
                StringUtil::encodeHTML($attachment->filename),
                $class,
                $image,
                $enlarge,
            );
        }

        return \sprintf(
            '<span class="%s">%s%s</span>',
            $class,
            $image,
            \str_contains($imageClasses, 'embeddedAttachmentLink') ? $enlarge : '',
        );
    }

    private function renderImageAsThumbnail(Attachment $attachment, string $outputType, mixed $thumbnail): bool
    {
        // Always use thumbnails for the simplified HTML output.
        if ($outputType == 'text/simplified-html') {
            return true;
        }

        // WCF 2.x permitted image resizing using exact pixel values. These
        // values are interpreted as a signal for the use of thumbnails.
        if (\is_numeric($thumbnail)) {
            if ($thumbnail === 0) {
                $thumbnail = true;
            } else {
                // Interpret the number as a request for the thumbnail if the
                // width matches or falls short of the thumbnail’s width.
                $thumbnail = ($attachment->thumbnailWidth >= $thumbnail);
            }
        } elseif ($thumbnail === 'false') {
            $thumbnail = false;
        } elseif ($thumbnail !== false) {
            $thumbnail = true;
        }

        // Force the use of the thumbnail if the user cannot access the full version.
        if (!$thumbnail && !$attachment->canDownload()) {
            $thumbnail = true;
        }

        return $thumbnail;
    }

    private function showVideoPlayer(Attachment $attachment): string
    {
        return WCF::getTPL()->fetch('__videoAttachmentBBCode', 'wcf', [
            'attachment' => $attachment,
            'attachmentIdentifier' => StringUtil::getRandomID(),
        ]);
    }

    private function showAudioPlayer(Attachment $attachment): string
    {
        return WCF::getTPL()->fetch('__audioAttachmentBBCode', 'wcf', [
            'attachment' => $attachment,
            'attachmentIdentifier' => StringUtil::getRandomID(),
        ]);
    }

    private function getAttachment(int $attachmentID): ?Attachment
    {
        if (!$attachmentID) {
            return null;
        }

        return MessageEmbeddedObjectManager::getInstance()->getObject(
            'com.woltlab.wcf.attachment',
            $attachmentID
        );
    }
}
