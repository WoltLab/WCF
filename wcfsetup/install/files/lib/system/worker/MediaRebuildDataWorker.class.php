<?php

namespace wcf\system\worker;

use wcf\data\media\MediaAction;
use wcf\data\media\MediaList;
use wcf\system\exception\SystemException;

/**
 * Worker implementation for updating media thumbnails.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractRebuildDataWorker<MediaList>
 */
class MediaRebuildDataWorker extends AbstractRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = MediaList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 10;

    #[\Override]
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlOrderBy = 'media.mediaID';
        $this->objectList->getConditionBuilder()->add('media.isImage = ?', [1]);
    }

    #[\Override]
    public function execute()
    {
        parent::execute();

        foreach ($this->objectList as $media) {
            try {
                (new MediaAction([$media], 'generateThumbnails'))->executeAction();
            } catch (SystemException $e) {
            }
        }
    }
}
