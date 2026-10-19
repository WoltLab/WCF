<?php

namespace wcf\page;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\data\media\Media;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\util\StringUtil;

/**
 * Redirects to the physical file or a thumbnail of a media file. The link to
 * this page remains stable even if the physical file of the media file is
 * replaced.
 *
 * Media files that have not been migrated to the new upload API yet cannot be
 * accessed until the media files have been rebuilt.
 *
 * @author  Matthias Schmidt, Marcel Werk, Tim Düsterhus, Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Only kept to resolve existing links, use `Media::getLink()` or `Media::getThumbnailLink()` instead.
 */
class MediaPage extends AbstractPage
{
    const AVAILABLE_DURING_OFFLINE_MODE = true;

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
     * link to the physical file or the requested thumbnail
     * @since 6.3
     */
    protected string $fileLink = '';

    /**
     * @inheritDoc
     */
    public $useTemplate = false;

    #[\Override]
    public function readData()
    {
        parent::readData();

        $file = $this->media->getFile();
        if ($file === null) {
            throw new IllegalLinkException();
        }

        if ($this->thumbnail !== '') {
            $this->fileLink = $this->media->getThumbnailLink($this->thumbnail);
        } elseif ($file->isStaticFile()) {
            $this->fileLink = $this->media->getFullSizeImageSource();
        } else {
            // The download is tracked by the file processor.
            $this->fileLink = $file->getLink();
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
    }

    #[\Override]
    public function show()
    {
        parent::show();

        $this->setPsr7Response(new RedirectResponse($this->fileLink));
    }
}
