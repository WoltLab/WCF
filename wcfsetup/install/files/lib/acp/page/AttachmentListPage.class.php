<?php

namespace wcf\acp\page;

use wcf\data\attachment\AdministrativeAttachmentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of attachments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    AdministrativeAttachmentList $objectList
 */
class AttachmentListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.attachment.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.attachment.canManageAttachment'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'uploadTime';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['attachmentID', 'filename', 'filesize', 'uploadTime'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = AdministrativeAttachmentList::class;

    /**
     * username
     * @var string
     */
    public $username = '';

    /**
     * filename
     * @var string
     */
    public $filename = '';

    /**
     * file type
     * @var string
     */
    public $fileType = '';

    /**
     * available file types
     * @var string[]
     */
    public $availableFileTypes = [];

    /**
     * @var int
     */
    public $attachmentID = 0;

    /**
     * attachment stats
     * @var array
     */
    public $stats = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['username'])) {
            $this->username = StringUtil::trim($_REQUEST['username']);
        }
        if (!empty($_REQUEST['filename'])) {
            $this->filename = StringUtil::trim($_REQUEST['filename']);
        }
        if (!empty($_REQUEST['fileType'])) {
            $this->fileType = $_REQUEST['fileType'];
        }
        if (!empty($_REQUEST['attachmentID'])) {
            $this->attachmentID = \intval($_REQUEST['attachmentID']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $objectTypeIDs = [];
        foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.attachment.objectType') as $objectType) {
            if (!$objectType->private) {
                $objectTypeIDs[] = $objectType->objectTypeID;
            }
        }

        if (!empty($objectTypeIDs)) {
            $this->objectList->getConditionBuilder()->add('attachment.objectTypeID IN (?)', [$objectTypeIDs]);
        } else {
            $this->objectList->getConditionBuilder()->add('1 = 0');
        }
        $this->objectList->getConditionBuilder()->add("attachment.tmpHash = ''");

        // get data
        $this->stats = $this->objectList->getStats();
        $this->availableFileTypes = $this->objectList->getAvailableFileTypes();

        // filter
        if (!empty($this->username)) {
            $user = User::getUserByUsername($this->username);
            if ($user->userID) {
                $this->objectList->getConditionBuilder()->add('attachment.userID = ?', [$user->userID]);
            }
        }
        if (!empty($this->filename)) {
            $this->objectList->getConditionBuilder()->add('attachment.filename LIKE ?', [$this->filename . '%']);
        }
        if (!empty($this->fileType)) {
            $this->objectList->getConditionBuilder()->add('attachment.fileType LIKE ?', [$this->fileType]);
        }
        if ($this->attachmentID) {
            $this->objectList->getConditionBuilder()->add('attachment.attachmentID = ?', [$this->attachmentID]);
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'stats' => $this->stats,
            'username' => $this->username,
            'filename' => $this->filename,
            'fileType' => $this->fileType,
            'availableFileTypes' => $this->availableFileTypes,
            'attachmentID' => $this->attachmentID,
        ]);
    }
}
