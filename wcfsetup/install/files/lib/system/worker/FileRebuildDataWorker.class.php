<?php

namespace wcf\system\worker;

use wcf\data\attachment\AttachmentAction;
use wcf\data\file\FileList;
use wcf\system\exception\SystemException;
use wcf\system\file\processor\FileProcessor;

/**
 * Worker implementation for updating files.
 *
 * @author Alexander Ebert
 * @copyright 2001-2014 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @method FileList getObjectList()
 * @property-read FileList $objectList
 */
final class FileRebuildDataWorker extends AbstractRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = FileList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 10;

    #[\Override]
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlOrderBy = 'file.fileID';
    }

    #[\Override]
    public function execute()
    {
        parent::execute();

        foreach ($this->objectList as $file) {
            FileProcessor::getInstance()->generateThumbnails($file);
        }
    }
}
