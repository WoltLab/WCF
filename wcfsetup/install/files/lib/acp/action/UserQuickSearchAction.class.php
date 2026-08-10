<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\RedirectResponse;
use wcf\acp\page\UserListPage;
use wcf\action\AbstractAction;
use wcf\data\search\SearchEditor;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\NamedUserException;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Provides special search options.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class UserQuickSearchAction extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canSearchUser'];

    /**
     * search mode
     */
    public string $mode = '';

    /**
     * matches
     * @var int[]
     */
    public array $matches = [];

    /**
     * results per page
     */
    public int $itemsPerPage = 50;

    /**
     * shown columns
     * @var string[]
     */
    public array $columns = ['registrationDate', 'lastActivityTime'];

    /**
     * sort field
     */
    public string $sortField = 'username';

    /**
     * sort order
     */
    public string $sortOrder = 'ASC';

    /**
     * number of results
     */
    public int $maxResults = 2000;

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        if (isset($_REQUEST['mode'])) {
            $this->mode = $_REQUEST['mode'];
        }
    }

    #[\Override]
    public function execute(): RedirectResponse
    {
        ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.user.search');

        parent::execute();

        // add email column for authorized users
        if (WCF::getSession()->hasPermission('admin.user.canEditMailAddress')) {
            \array_unshift($this->columns, 'email');
        }

        switch ($this->mode) {
            case 'banned':
                $sql = "SELECT      user_table.userID
                        FROM        wcf1_user user_table
                        LEFT JOIN   wcf1_user_option_value option_value
                        ON          option_value.userID = user_table.userID
                        WHERE       banned = ?";
                $statement = WCF::getDB()->prepare($sql, $this->maxResults);
                $statement->execute([1]);
                $this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
                break;

            case 'newest':
                $this->maxResults = 100;
                $this->sortField = 'registrationDate';
                $this->sortOrder = 'DESC';
                $sql = "SELECT      user_table.userID
                        FROM        wcf1_user user_table
                        LEFT JOIN   wcf1_user_option_value option_value
                        ON          option_value.userID = user_table.userID
                        ORDER BY    user_table.registrationDate DESC";
                $statement = WCF::getDB()->prepare($sql, $this->maxResults);
                $statement->execute();
                $this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
                break;

            case 'disabled':
                $this->sortField = 'registrationDate';
                $this->sortOrder = 'DESC';
                $sql = "SELECT      user_table.userID
                        FROM        wcf1_user user_table
                        LEFT JOIN   wcf1_user_option_value option_value
                        ON          option_value.userID = user_table.userID
                        WHERE       activationCode <> ?
                        ORDER BY    user_table.registrationDate DESC";
                $statement = WCF::getDB()->prepare($sql, $this->maxResults);
                $statement->execute([0]);
                $this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
                break;

            case 'pendingActivation':
                $conditionBuilder = new PreparedStatementConditionBuilder();
                $conditionBuilder->add('banned = ?', [0]);
                $conditionBuilder->add('activationCode <> ?', [0]);
                if (((int)\REGISTER_ACTIVATION_METHOD & User::REGISTER_ACTIVATION_USER) !== 0) {
                    $conditionBuilder->add('emailConfirmed IS NULL');
                }

                $this->sortField = 'registrationDate';
                $this->sortOrder = 'DESC';
                $sql = "SELECT      user_table.userID
                        FROM        wcf1_user user_table
                        LEFT JOIN   wcf1_user_option_value option_value
                        ON          option_value.userID = user_table.userID
                        " . $conditionBuilder . "
                        ORDER BY    user_table.registrationDate DESC";
                $statement = WCF::getDB()->prepare($sql, $this->maxResults);
                $statement->execute($conditionBuilder->getParameters());
                $this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
                break;

            case 'disabledAvatars':
                $sql = "SELECT      user_table.userID
                        FROM        wcf1_user user_table
                        LEFT JOIN   wcf1_user_option_value option_value
                        ON          option_value.userID = user_table.userID
                        WHERE       disableAvatar = ?";
                $statement = WCF::getDB()->prepare($sql, $this->maxResults);
                $statement->execute([1]);
                $this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
                break;

            case 'disabledSignatures':
                $sql = "SELECT      user_table.userID
                        FROM        wcf1_user user_table
                        LEFT JOIN   wcf1_user_option_value option_value
                        ON          option_value.userID = user_table.userID
                        WHERE       disableSignature = ?";
                $statement = WCF::getDB()->prepare($sql, $this->maxResults);
                $statement->execute([1]);
                $this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
                break;
        }

        if (empty($this->matches)) {
            throw new NamedUserException(
                HtmlString::fromSafeHtml(WCF::getLanguage()->get('wcf.acp.user.search.error.noMatches'))
            );
        }

        // store search result in database
        $data = \serialize([
            'matches' => $this->matches,
            'itemsPerPage' => $this->itemsPerPage,
            'columns' => $this->columns,
        ]);

        $search = SearchEditor::create([
            'userID' => WCF::getUser()->userID,
            'searchData' => $data,
            'searchTime' => \TIME_NOW,
            'searchType' => 'users',
        ]);
        $this->executed();

        // forward to result page
        $url = LinkHandler::getInstance()->getControllerLink(
            UserListPage::class,
            ['id' => $search->searchID],
            'sortField=' . \rawurlencode($this->sortField) . '&sortOrder=' . \rawurlencode($this->sortOrder)
        );

        return new RedirectResponse(
            $url
        );
    }
}
