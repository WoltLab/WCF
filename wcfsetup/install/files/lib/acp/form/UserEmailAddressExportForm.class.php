<?php

namespace wcf\acp\form;

use wcf\data\user\User;
use wcf\form\AbstractForm;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the export user mail addresses form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserEmailAddressExportForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canEditMailAddress'];

    /**
     * type of the file containing the exported email addresses
     * @var string
     */
    public $fileType = 'csv';

    /**
     * ids of the users whose email addresses are exported
     * @var int[]
     */
    public $userIDs = [];

    /**
     * string used to separate email adresses
     * @var string
     */
    public $separator = ',';

    /**
     * string used to wrap email adresses
     * @var string
     */
    public $textSeparator = '"';

    /**
     * users whose email addresses are exported
     * @var User[]
     */
    public $users = [];

    /**
     * clipboard item type id
     * @var int
     */
    protected $objectTypeID;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // get object type id
        $this->objectTypeID = ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user');
        if ($this->objectTypeID === null) {
            throw new SystemException("Unknown clipboard item type 'com.woltlab.wcf.user'");
        }

        // get user ids
        $users = ClipboardHandler::getInstance()->getMarkedItems($this->objectTypeID);
        if (empty($users)) {
            throw new IllegalLinkException();
        }

        // load users
        $this->userIDs = \array_keys($users);
        $this->users = $users;
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['fileType']) && $_POST['fileType'] == 'xml') {
            $this->fileType = $_POST['fileType'];
        }
        if (isset($_POST['separator'])) {
            $this->separator = $_POST['separator'];
        }
        if (isset($_POST['textSeparator'])) {
            $this->textSeparator = $_POST['textSeparator'];
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // send content type
        \header('Content-Type: text/' . $this->fileType . '; charset=UTF-8');
        \header('Content-Disposition: attachment; filename="export.' . $this->fileType . '"');

        if ($this->fileType == 'xml') {
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<addresses>\n";
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->userIDs]);

        // count users
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_user
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $count = $statement->fetchSingleColumn();

        // get users
        $sql = "SELECT      email
                FROM        wcf1_user
                " . $conditions . "
                ORDER BY    email";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        $i = 0;
        while ($row = $statement->fetchArray()) {
            if ($this->fileType == 'xml') {
                echo "<address><![CDATA[" . StringUtil::escapeCDATA($row['email']) . "]]></address>\n";
            } else {
                echo $this->textSeparator . $row['email'] . $this->textSeparator . ($i < $count ? $this->separator : '');
            }
            $i++;
        }

        if ($this->fileType == 'xml') {
            echo "</addresses>";
        }

        $this->saved();

        // remove items
        ClipboardHandler::getInstance()->removeItems($this->objectTypeID);

        exit;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'users' => $this->users,
            'separator' => $this->separator,
            'textSeparator' => $this->textSeparator,
            'fileType' => $this->fileType,
        ]);
    }
}
