<?php

namespace wcf\page;

use Laminas\Diactoros\Response\EmptyResponse;
use wcf\data\media\Media;
use wcf\data\media\MediaEditor;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\util\FileReader;
use wcf\util\StringUtil;

/**
 * Shows a media file.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MediaPage extends AbstractPage
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

    /**
     * etag for the media file
     * @var ?string
     */
    public $eTag;

    /**
     * file reader object
     * @var ?FileReader
     */
    public $fileReader;

    /**
     * requested media file
     * @var ?Media
     */
    public $media;

    /**
     * size of the requested thumbnail
     * @var string
     */
    public $thumbnail = '';

    /**
     * @inheritDoc
     */
    public $useTemplate = false;

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

    #[\Override]
    public function readData()
    {
        parent::readData();

        // get file data
        if ($this->thumbnail !== '') {
            $mimeType = $this->media->{$this->thumbnail . 'ThumbnailType'};
            $filesize = $this->media->{$this->thumbnail . 'ThumbnailSize'};
            $location = $this->media->getThumbnailLocation($this->thumbnail);
            $this->eTag = \strtoupper($this->thumbnail) . '_' . $this->media->mediaID;
        } else {
            $mimeType = $this->media->fileType;
            $filesize = $this->media->filesize;
            $location = $this->media->getLocation();
            $this->eTag = (string)$this->media->mediaID;
        }

        $this->eTag .= '_' . $this->media->fileHash;

        // init file reader
        $maxAge = 3600;
        $this->fileReader = new FileReader($location, [
            'filename' => $this->media->filename,
            'mimeType' => $mimeType,
            'filesize' => $filesize,
            'showInline' => \in_array($mimeType, self::$inlineMimeTypes, true),
            'enableRangeSupport' => $this->thumbnail !== '',
            'lastModificationTime' => $this->media->fileUpdateTime ?? $this->media->uploadTime,
            'expirationDate' => \TIME_NOW + $maxAge,
            'maxAge' => $maxAge,
        ]);

        if ($this->eTag !== null) {
            $this->fileReader->addHeader('ETag', '"' . $this->eTag . '"');
        }
    }

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->media = Helper::fetchObjectFromQueryParameter(Media::class);
        if (!$this->media->isAccessible()) {
            throw new PermissionDeniedException();
        }

        if (isset($_REQUEST['thumbnail'])) {
            $this->thumbnail = StringUtil::trim($_REQUEST['thumbnail']);
        }
        if ($this->thumbnail === 'original') {
            // The 'original' size is required by the editor, but is not a valid thumbnail size.
            $this->thumbnail = '';
        }
        if ($this->thumbnail !== '' && !isset(Media::getThumbnailSizes()[$this->thumbnail])) {
            throw new IllegalLinkException();
        }

        if ($this->thumbnail !== '' && !$this->media->{$this->thumbnail . 'ThumbnailType'}) {
            $this->thumbnail = '';
        }
    }

    #[\Override]
    public function show()
    {
        parent::show();

        // etag caching
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $this->eTag . '"') {
            $this->setPsr7Response(new EmptyResponse(304));

            return;
        }

        if ($this->thumbnail === '') {
            // update download count
            (new MediaEditor($this->media))->update([
                'downloads' => $this->media->downloads + 1,
                'lastDownloadTime' => \TIME_NOW,
            ]);
        }

        // send file to client
        $this->fileReader->send();

        exit;
    }
}
