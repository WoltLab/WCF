<?php

namespace wcf\page;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentBuilder;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
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
     * attachment object
     * @var ?Attachment
     */
    public $attachment;

    /**
     * shows the tiny thumbnail
     * @var int
     */
    public $tiny = 0;

    /**
     * shows the standard thumbnail
     * @var int
     */
    public $thumbnail = 0;

    /**
     * file reader object
     * @var ?FileReader
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
     * @var ?string
     */
    public $eTag;

    /**
     * @var string
     */
    public $controllerName = 'Attachment';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->attachment = Helper::fetchObjectFromQueryParameter(Attachment::class);

        $parameters = ['object' => $this->attachment];
        if (isset($_REQUEST['tiny']) && $this->attachment->tinyThumbnailType !== '') {
            $this->tiny = \intval($_REQUEST['tiny']);
            $parameters['tiny'] = $this->tiny;
        }
        if (isset($_REQUEST['thumbnail']) && $this->attachment->thumbnailType !== '') {
            $this->thumbnail = \intval($_REQUEST['thumbnail']);
            $parameters['thumbnail'] = $this->thumbnail;
        }

        $this->canonicalURL = LinkHandler::getInstance()->getLink($this->controllerName, $parameters);
    }

    #[\Override]
    public function checkPermissions()
    {
        parent::checkPermissions();

        if ($this->attachment->tmpHash !== '') {
            if ($this->attachment->userID !== null && $this->attachment->userID !== WCF::getUser()->userID) {
                throw new IllegalLinkException();
            }
        } else {
            // check permissions
            if ($this->tiny !== 0 || $this->thumbnail !== 0) {
                if (!$this->attachment->canViewPreview()) {
                    throw new PermissionDeniedException();
                }
            } elseif (!$this->attachment->canDownload()) {
                throw new PermissionDeniedException();
            }
        }
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        // The redirect is placed here instead of inside the `readParameters()`
        // method in order to take advantage of the previous access validation.
        if ($this->attachment->getFile() !== null) {
            if ($this->tiny !== 0) {
                $url = $this->attachment->getThumbnailLink('tiny');
            } elseif ($this->thumbnail !== 0) {
                $url = $this->attachment->getThumbnailLink();
            } else {
                $url = $this->attachment->getLink();
            }

            $this->setPsr7Response(new RedirectResponse($url, 303));

            return;
        }

        // get file data
        if ($this->tiny !== 0) {
            $mimeType = $this->attachment->tinyThumbnailType;
            $filesize = $this->attachment->tinyThumbnailSize;
            $location = $this->attachment->getTinyThumbnailLocation();
            $this->eTag = 'TINY_' . $this->attachment->attachmentID;
        } elseif ($this->thumbnail !== 0) {
            $mimeType = $this->attachment->thumbnailType;
            $filesize = $this->attachment->thumbnailSize;
            $location = $this->attachment->getThumbnailLocation();
            $this->eTag = 'THUMB_' . $this->attachment->attachmentID;
        } else {
            $mimeType = $this->attachment->fileType;
            $filesize = $this->attachment->filesize;
            $location = $this->attachment->getLocation();
            $this->eTag = (string)$this->attachment->attachmentID;
        }

        // unsaved attachments may be cached by the browser for up to 5 minutes only
        $cacheDuration = ($this->attachment->tmpHash !== '') ? 300 : 31536000;

        // init file reader
        try {
            $this->fileReader = new FileReader($location, [
                'filename' => $this->attachment->filename,
                'mimeType' => $mimeType,
                'filesize' => $filesize,
                'showInline' => \in_array($mimeType, self::$inlineMimeTypes, true),
                'enableRangeSupport' => $this->tiny === 0 && $this->thumbnail === 0,
                'lastModificationTime' => $this->attachment->uploadTime,
                'expirationDate' => \TIME_NOW + $cacheDuration,
                'maxAge' => $cacheDuration,
            ]);
        } catch (SystemException $e) {
            if ($e->getMessage() !== 'Location of file is not set or invalid') {
                throw $e;
            }

            throw new IllegalLinkException();
        }

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

    #[\Override]
    public function show()
    {
        parent::show();

        if ($this->attachment->getFile() !== null) {
            return;
        }

        // etag caching
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $this->eTag . '"') {
            $this->setPsr7Response(new EmptyResponse(304));

            return;
        }

        if ($this->tiny === 0 && $this->thumbnail === 0) {
            // update download count
            AttachmentBuilder::forUpdate($this->attachment)
                ->incrementDownloads(1)
                ->setLastDownloadTime(\TIME_NOW)
                ->update();
        }

        // send file to client
        $this->fileReader->send();

        exit;
    }
}
