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
        // get attachment id
        $attachmentID = 0;
        if (isset($openingTag['attributes'][0])) {
            $attachmentID = $openingTag['attributes'][0];
        }

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

        /** @var Attachment $attachment */
        $attachment = MessageEmbeddedObjectManager::getInstance()->getObject(
            'com.woltlab.wcf.attachment',
            $attachmentID
        );

        if ($attachment !== null) {
            if ($attachment->showAsImage() && $attachment->canViewPreview() && ($parser->getOutputType() == 'text/html' || $parser->getOutputType() == 'text/simplified-html')) {
                // image
                $alignment = ($openingTag['attributes'][1] ?? '');
                $thumbnail = ($openingTag['attributes'][2] ?? false);

                // backward compatibility, check if width is larger than thumbnail's width to display full version
                if (\is_numeric($thumbnail)) {
                    if ($thumbnail == 0) {
                        $thumbnail = true;
                    } else {
                        // true if supplied width is smaller or equal to thumbnail's width
                        $thumbnail = ($attachment->thumbnailWidth >= $thumbnail) ? true : false;
                    }
                } elseif ($thumbnail === 'false') {
                    $thumbnail = false;
                } elseif ($thumbnail !== false) {
                    $thumbnail = true;
                }

                // always use thumbnail in simplified version
                if ($parser->getOutputType() == 'text/simplified-html') {
                    $thumbnail = true;
                }

                // Force the use of the thumbnail if the user cannot access the full version.
                if (!$thumbnail && !$attachment->canDownload()) {
                    $thumbnail = true;
                }

                if (!$thumbnail) {
                    $class = '';
                    if ($alignment == 'left' || $alignment == 'right') {
                        $class = 'messageFloatObject' . \ucfirst($alignment);
                    }

                    $source = StringUtil::encodeHTML($attachment->getLink());
                    $title = StringUtil::encodeHTML($attachment->filename);
                    $result = '<img src="' . $source . '" width="' . $attachment->width . '" height="' . $attachment->height . '" alt="">';

                    if (!$hasParentLink && ($attachment->width > ATTACHMENT_THUMBNAIL_WIDTH || $attachment->height > ATTACHMENT_THUMBNAIL_HEIGHT)) {
                        $icon = FontAwesomeIcon::fromValues('magnifying-glass')->toHtml(24);
                        $result = <<<HTML
                        <a href="{$source}" title="{$title}" class="embeddedAttachmentLink jsImageViewer {$class}'">
                            {$result}
                            <span class="embeddedAttachmentLinkEnlarge">
                                {$icon}
                            </span>
                        </a>
                        HTML;
                    } else {
                        $result = '<span title="' . $title . '"' . ($class ? (' class="' . $class . '"') : '') . '>' . $result . '</span>';
                    }
                } else {
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

                    $result = '<img src="' . StringUtil::encodeHTML(LinkHandler::getInstance()->getLink(
                        'Attachment',
                        $linkParameters
                    )) . '"' . ($imageClasses ? ' class="' . $imageClasses . '"' : '') . ' width="' . ($attachment->hasThumbnail() ? $attachment->thumbnailWidth : $attachment->width) . '" height="' . ($attachment->hasThumbnail() ? $attachment->thumbnailHeight : $attachment->height) . '" alt="" loading="lazy">';

                    if (!$hasParentLink && $attachment->hasThumbnail() && $attachment->canDownload()) {
                        $result = '<a href="' . StringUtil::encodeHTML(LinkHandler::getInstance()->getLink(
                            'Attachment',
                            ['object' => $attachment]
                        )) . '" title="' . StringUtil::encodeHTML($attachment->filename) . '" class="embeddedAttachmentLink jsImageViewer' . ($class ? ' ' . $class : '') . '">' . $result . $enlarge . '</a>';
                    } else {
                        if (\str_contains($imageClasses, 'embeddedAttachmentLink')) {
                            $result = $result . $enlarge;
                        }

                        $result = '<span' . ($class ? (' class="' . $class . '"') : '') . '>' . $result . '</span>';
                    }
                }

                return $result;
            } elseif (\substr($attachment->fileType, 0, 6) === 'video/' && $parser->getOutputType() == 'text/html') {
                return WCF::getTPL()->fetch('__videoAttachmentBBCode', 'wcf', [
                    'attachment' => $attachment,
                    'attachmentIdentifier' => StringUtil::getRandomID(),
                ]);
            } elseif (\substr($attachment->fileType, 0, 6) === 'audio/' && $parser->getOutputType() == 'text/html') {
                return WCF::getTPL()->fetch('__audioAttachmentBBCode', 'wcf', [
                    'attachment' => $attachment,
                    'attachmentIdentifier' => StringUtil::getRandomID(),
                ]);
            } else {
                // file
                return StringUtil::getAnchorTag($attachment->getLink(), $attachment->filename);
            }
        }

        // fallback
        return StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Attachment', [
            'id' => $attachmentID,
        ]));
    }
}
