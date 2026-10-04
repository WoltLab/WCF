<?php

namespace wcf\acp\form;

use wcf\data\user\User;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
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
    use TMultipleUsersForm;

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

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->users = $this->readUsersFromRequest();
        $this->userIDs = \array_keys($this->users);
    }

    #[\Override]
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['fileType']) && $_POST['fileType'] === 'xml') {
            $this->fileType = $_POST['fileType'];
        }
        if (isset($_POST['separator'])) {
            $this->separator = $_POST['separator'];
        }
        if (isset($_POST['textSeparator'])) {
            $this->textSeparator = $_POST['textSeparator'];
        }
    }

    #[\Override]
    public function save()
    {
        parent::save();

        // send content type
        \header('Content-Type: text/' . $this->fileType . '; charset=UTF-8');
        \header('Content-Disposition: attachment; filename="export.' . $this->fileType . '"');

        if ($this->fileType === 'xml') {
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<users>\n";
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("userID IN (?)", [$this->userIDs]);

        // get users
        $sql = "SELECT      username, email
                FROM        wcf1_user
                " . $conditions . "
                ORDER BY    email";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        while ($row = $statement->fetchArray()) {
            if ($this->fileType === 'xml') {
                echo "<user><username><![CDATA[" . StringUtil::escapeCDATA($row['username']) . "]]></username><email><![CDATA[" . StringUtil::escapeCDATA($row['email']) . "]]></email></user>\n";
            } else {
                echo $this->textSeparator . \str_replace($this->textSeparator, $this->textSeparator . $this->textSeparator, $row['username']) . $this->textSeparator . $this->separator . $this->textSeparator . \str_replace($this->textSeparator, $this->textSeparator . $this->textSeparator, $row['email']) . $this->textSeparator . "\n";
            }
        }

        if ($this->fileType === 'xml') {
            echo "</users>";
        }

        $this->saved();

        exit;
    }

    #[\Override]
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
