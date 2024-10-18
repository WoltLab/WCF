<?php

namespace wcf\form;

use wcf\acp\form\UserOptionListForm;
use wcf\data\search\SearchEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the user search form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property    UserOptionHandler $optionHandler
 */
class UserSearchForm extends UserOptionListForm
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_MEMBERS_LIST'];

    /**
     * username
     * @var string
     */
    public $username = '';

    /**
     * matches
     * @var int[]
     */
    public $matches = [];

    /**
     * condition builder object
     * @var PreparedStatementConditionBuilder
     */
    public $conditions;

    /**
     * search id
     * @var int
     */
    public $searchID = 0;

    /**
     * number of results
     * @var int
     */
    public $maxResults = 1000;

    /**
     * option tree
     * @var array
     */
    public $optionTree = [];

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['username'])) {
            $this->username = StringUtil::trim($_POST['username']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function initOptionHandler()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->optionHandler->enableSearchMode();
        $this->optionHandler->init();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->readOptionTree();

        // add breadcrumbs
        if (MODULE_MEMBERS_LIST) {
            PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.MembersList');
        }
    }

    /**
     * Reads option tree on page init.
     */
    protected function readOptionTree()
    {
        $this->optionTree = $this->optionHandler->getOptionTree();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'username' => $this->username,
            'optionTree' => $this->optionTree,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // store search result in database
        $search = SearchEditor::create([
            'userID' => WCF::getUser()->userID ?: null,
            'searchData' => \serialize(['matches' => $this->matches]),
            'searchTime' => TIME_NOW,
            'searchType' => 'users',
        ]);

        // get new search id
        $this->searchID = $search->searchID;
        $this->saved();

        // forward to result page
        $url = LinkHandler::getInstance()->getLink('MembersList', ['id' => $this->searchID]);
        HeaderUtil::redirect($url);

        exit;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        AbstractForm::validate();

        // do search
        $this->search();

        if (empty($this->matches)) {
            throw new UserInputException('search', 'noMatches');
        }
    }

    /**
     * Search for users which fit to the search values.
     */
    protected function search()
    {
        $this->matches = [];

        // build search condition
        $this->conditions = new PreparedStatementConditionBuilder();

        // static fields
        $this->buildStaticConditions();

        // dynamic fields
        $this->buildDynamicConditions();

        // if no conditions exists, no need to send query
        if (!$this->conditions->__toString()) {
            return;
        }

        // perform search
        $sql = "SELECT      user_table.userID
                FROM        wcf1_user user_table
                LEFT JOIN   wcf1_user_option_value option_value
                ON          option_value.userID = user_table.userID
                {$this->conditions}";
        $statement = WCF::getDB()->prepare($sql, $this->maxResults);
        $statement->execute($this->conditions->getParameters());
        $this->matches = $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Builds the static conditions.
     */
    protected function buildStaticConditions()
    {
        if (!empty($this->username)) {
            $this->conditions->add("user_table.username LIKE ?", ['%' . \addcslashes($this->username, '_%') . '%']);
        }
    }

    /**
     * Builds the dynamic conditions.
     */
    protected function buildDynamicConditions()
    {
        foreach ($this->optionHandler->getCategoryOptions('profile') as $option) {
            $option = $option['object'];

            $value = $this->optionHandler->optionValues[$option->optionName] ?? null;
            /** @noinspection PhpUndefinedMethodInspection */
            $this->optionHandler->getTypeObject($option->optionType)->getCondition($this->conditions, $option, $value);
        }
    }
}
