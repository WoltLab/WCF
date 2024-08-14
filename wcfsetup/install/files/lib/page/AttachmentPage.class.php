<?php

namespace wcf\page;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileReader;

/**
 * Shows an attachment.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.1 Attachments are now served through the unified upload system
 */
class AttachmentPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $useTemplate = false;

    /**
     * attachment id
     * @var int
     */
    public $attachmentID = 0;

    /**
     * attachment object
     * @var Attachment
     */
    public $attachment;

    /**
     * shows the tiny thumbnail
     * @var bool
     */
    public $tiny = 0;

    /**
     * shows the standard thumbnail
     * @var bool
     */
    public $thumbnail = 0;

    /**
     * file reader object
     * @var FileReader
     */
    public $fileReader;

    /**
     * list of mime types which belong to files that are displayed inline
     * @var string[]
     */
    public static $inlineMimeTypes = [
        'image/gif',
        'image/jpeg',
        'image/png',
        'image/x-png',
        'application/pdf',
        'image/pjpeg',
        'image/webp',
    ];

    /**
     * etag for this attachment
     * @var string
     */
    public $eTag;

    /**
     * @var string
     */
    public $controllerName = 'Attachment';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->attachmentID = \intval($_REQUEST['id']);
        }
        $this->attachment = new Attachment($this->attachmentID);
        if (!$this->attachment->attachmentID) {
            throw new IllegalLinkException();
        }

        $parameters = ['object' => $this->attachment];
        if (isset($_REQUEST['tiny']) && $this->attachment->tinyThumbnailType) {
            $this->tiny = \intval($_REQUEST['tiny']);
            $parameters['tiny'] = $this->tiny;
        }
        if (isset($_REQUEST['thumbnail']) && $this->attachment->thumbnailType) {
            $this->thumbnail = \intval($_REQUEST['thumbnail']);
            $parameters['thumbnail'] = $this->thumbnail;
        }

        $this->canonicalURL = LinkHandler::getInstance()->getLink($this->controllerName, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        parent::checkPermissions();

        if ($this->attachment->tmpHash) {
            if ($this->attachment->userID && $this->attachment->userID != WCF::getUser()->userID) {
                throw new IllegalLinkException();
            }
        } else {
            // check permissions
            if ($this->tiny || $this->thumbnail) {
                if (!$this->attachment->canViewPreview()) {
                    throw new PermissionDeniedException();
                }
            } elseif (!$this->attachment->canDownload()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // The redirect is placed here instead of inside the `readParameters()`
        // method in order to take advantage of the previous access validation.
        if ($this->attachment->getFile() !== null) {
            if ($this->tiny) {
                $url = $this->attachment->getThumbnailLink('tiny');
            } elseif ($this->thumbnail) {
                $url = $this->attachment->getThumbnailLink();
            } else {
                $url = $this->attachment->getLink();
            }

            return new RedirectResponse($url);
        }

        // get file data
        if ($this->tiny) {
            $mimeType = $this->attachment->tinyThumbnailType;
            $filesize = $this->attachment->tinyThumbnailSize;
            $location = $this->attachment->getTinyThumbnailLocation();
            $this->eTag = 'TINY_' . $this->attachmentID;
        } elseif ($this->thumbnail) {
            $mimeType = $this->attachment->thumbnailType;
            $filesize = $this->attachment->thumbnailSize;
            $location = $this->attachment->getThumbnailLocation();
            $this->eTag = 'THUMB_' . $this->attachmentID;
        } else {
            $mimeType = $this->attachment->fileType;
            $filesize = $this->attachment->filesize;
            $location = $this->attachment->getLocation();
            $this->eTag = $this->attachmentID;
        }

        // unsaved attachments may be cached by the browser for up to 5 minutes only
        $cacheDuration = ($this->attachment->tmpHash) ? 300 : 31536000;

        // init file reader
        $this->fileReader = new FileReader($location, [
            'filename' => $this->attachment->filename,
            'mimeType' => $mimeType,
            'filesize' => $filesize,
            'showInline' => \in_array($mimeType, self::$inlineMimeTypes),
            'enableRangeSupport' => !$this->tiny && !$this->thumbnail,
            'lastModificationTime' => $this->attachment->uploadTime,
            'expirationDate' => TIME_NOW + $cacheDuration,
            'maxAge' => $cacheDuration,
        ]);

        // Prevent <script> execution in the context of the community's domain if
        // an attacker somehow bypasses 'content-disposition: attachment' for non-inline
        // MIME-Types. One possibility might be a package extending $inlineMimeTypes
        // in an unsafe fashion.
        //
        // Allow style-src 'unsafe-inline', because otherwise the integrated PDF viewer
        // of Safari will fail to apply its own trusted stylesheet.
        $this->fileReader->addHeader('content-security-policy', "default-src 'none'; style-src 'unsafe-inline';");
        $this->fileReader->addHeader('x-content-type-options', 'nosniff');

        if ($this->eTag !== null) {
            $this->fileReader->addHeader('ETag', '"' . $this->eTag . '"');
        }
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        parent::show();

        if ($this->attachment->getFile() !== null) {
            return;
        }

        // etag caching
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == '"' . $this->eTag . '"') {
            return new EmptyResponse(304);
        }

        if (!$this->tiny && !$this->thumbnail) {
            // update download count
            $editor = new AttachmentEditor($this->attachment);
            $editor->update([
                'downloads' => $this->attachment->downloads + 1,
                'lastDownloadTime' => TIME_NOW,
            ]);
        }

        // send file to client
        $this->fileReader->send();

        exit;
    }
}
