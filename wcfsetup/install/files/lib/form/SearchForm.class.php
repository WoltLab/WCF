<?php

namespace wcf\form;

use wcf\data\search\keyword\SearchKeywordAction;
use wcf\data\search\Search;
use wcf\data\search\SearchAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchEngine;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the search form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 5.5
 */
class SearchForm extends AbstractCaptchaForm
{
    /**
     * list of additional conditions
     * @var string[]
     */
    public $additionalConditions = [];

    /**
     * end date
     * @var int
     */
    public $endDate = '';

    /**
     * true, if search should be modified
     * @var bool
     */
    public $modifySearch;

    /**
     * search id used for modification
     * @var int
     */
    public $modifySearchID = 0;

    /**
     * require exact matches
     * @var int
     */
    public $nameExactly = 1;

    /**
     * search query
     * @var string
     */
    public $query = '';

    /**
     * list of search results
     * @var array
     */
    public $results = [];

    /**
     * @inheritDoc
     */
    public $sortField = '';

    /**
     * @inheritDoc
     */
    public $sortOrder = '';

    /**
     * user id
     * @var int
     */
    public $userID = 0;

    /**
     * username
     * @var string
     */
    public $username = '';

    /**
     * parameters used for previous search
     * @var array
     */
    public $searchData = [];

    /**
     * search id
     * @var int
     */
    public $searchID = 0;

    /**
     * PreparedStatementConditionBuilder object
     * @var \wcf\system\database\util\PreparedStatementConditionBuilder
     */
    public $searchIndexCondition;

    /**
     * search hash to modify existing search
     * @var string
     */
    public $searchHash = '';

    /**
     * selected object types
     * @var string[]
     */
    public $selectedObjectTypes = [];

    /**
     * start date
     * @var int
     */
    public $startDate = '';

    /**
     * search for subject only
     * @var int
     */
    public $subjectOnly = 0;

    /**
     * mark as submitted form if modifying search
     * @var bool
     */
    public $submit = false;

    /**
     * @var int[]
     */
    public $userIDs = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['q'])) {
            $this->query = StringUtil::trim($_REQUEST['q']);
        }
        if (isset($_REQUEST['username'])) {
            $this->username = StringUtil::trim($_REQUEST['username']);
        }
        if (isset($_REQUEST['userID'])) {
            $this->userID = \intval($_REQUEST['userID']);
        }
        if (isset($_REQUEST['types']) && \is_array($_REQUEST['types'])) {
            $this->selectedObjectTypes = $_REQUEST['types'];

            // handle special selection to search in all areas
            if (isset($this->selectedObjectTypes[0]) && $this->selectedObjectTypes[0] === 'everywhere') {
                $this->selectedObjectTypes = [];
                foreach (SearchEngine::getInstance()->getAvailableObjectTypes() as $typeName => $typeObject) {
                    $this->selectedObjectTypes[] = $typeName;
                }
            } else {
                // validate given values
                foreach ($this->selectedObjectTypes as $objectTypeName) {
                    if (SearchEngine::getInstance()->getObjectType($objectTypeName) === null) {
                        throw new IllegalLinkException();
                    }
                }
            }
        }
        $this->submit = (!empty($_POST) || !empty($this->query) || !empty($this->username) || $this->userID);

        if (isset($_REQUEST['modify'])) {
            $this->modifySearchID = \intval($_REQUEST['modify']);
            $this->modifySearch = new Search($this->modifySearchID);

            if (!$this->modifySearch->searchID || ($this->modifySearch->userID && $this->modifySearch->userID != WCF::getUser()->userID)) {
                throw new IllegalLinkException();
            }

            $this->searchData = \unserialize($this->modifySearch->searchData);
            if (empty($this->searchData['alterable'])) {
                throw new IllegalLinkException();
            }
            $this->query = $this->searchData['query'];
            $this->sortOrder = $this->searchData['sortOrder'];
            $this->sortField = $this->searchData['sortField'];
            $this->nameExactly = $this->searchData['nameExactly'];
            $this->subjectOnly = $this->searchData['subjectOnly'];
            $this->startDate = $this->searchData['startDate'];
            $this->endDate = $this->searchData['endDate'];
            $this->username = $this->searchData['username'];
            $this->userID = $this->searchData['userID'];
            $this->selectedObjectTypes = $this->searchData['selectedObjectTypes'];

            if (!empty($_POST)) {
                $this->submit = true;
            }
        }

        // disable check for security token for GET requests
        if ($this->submit) {
            $_POST['t'] = WCF::getSession()->getSecurityToken();
        }

        // sort order
        if (isset($_REQUEST['sortField'])) {
            $this->sortField = $_REQUEST['sortField'];
        }

        switch ($this->sortField) {
            case 'subject':
            case 'time':
            case 'username':
                break;

            case 'relevance':
                if (!$this->submit || !empty($this->query)) {
                    break;
                }

                // no break
            default:
                if (!$this->submit || !empty($this->query)) {
                    $this->sortField = 'relevance';
                } else {
                    $this->sortField = 'time';
                }
        }

        if (isset($_REQUEST['sortOrder'])) {
            $this->sortOrder = $_REQUEST['sortOrder'];
            switch ($this->sortOrder) {
                case 'ASC':
                case 'DESC':
                    break;
                default:
                    $this->sortOrder = 'DESC';
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        $this->nameExactly = 0;
        if (isset($_POST['nameExactly'])) {
            $this->nameExactly = \intval($_POST['nameExactly']);
        }
        if (isset($_POST['subjectOnly'])) {
            $this->subjectOnly = \intval($_POST['subjectOnly']);
        }
        if (isset($_POST['startDate'])) {
            $this->startDate = $_POST['startDate'];
        }
        if (isset($_POST['endDate'])) {
            $this->endDate = $_POST['endDate'];
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // get search conditions
        $this->getConditions();

        // check query and author
        if (empty($this->query) && empty($this->username) && !$this->userID) {
            throw new UserInputException('q');
        }

        // build search hash
        $this->searchHash = \sha1(\serialize([
            $this->query,
            $this->selectedObjectTypes,
            !$this->subjectOnly,
            $this->searchIndexCondition,
            $this->additionalConditions,
            $this->sortField . ' ' . $this->sortOrder,
            PACKAGE_ID,
        ]));

        // check search hash
        if (!empty($this->query)) {
            $parameters = [$this->searchHash, 'messages', TIME_NOW - 1800];
            if (WCF::getUser()->userID) {
                $parameters[] = WCF::getUser()->userID;
            }

            $sql = "SELECT  searchID
                    FROM    wcf1_search
                    WHERE   searchHash = ?
                        AND searchType = ?
                        AND searchTime > ?
                        " . (WCF::getUser()->userID ? 'AND userID = ?' : 'AND userID IS NULL');
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($parameters);
            $row = $statement->fetchArray();
            if ($row !== false) {
                HeaderUtil::redirect(LinkHandler::getInstance()->getLink(
                    'SearchResult',
                    ['id' => $row['searchID']],
                    'highlight=' . \urlencode($this->query)
                ));

                exit;
            }
        }

        // do search
        $this->results = SearchEngine::getInstance()->search(
            $this->query,
            $this->selectedObjectTypes,
            $this->subjectOnly,
            $this->searchIndexCondition,
            $this->additionalConditions,
            $this->sortField . ' ' . $this->sortOrder
        );

        // result is empty
        if (empty($this->results)) {
            $this->throwNoMatchesException();
        }
    }

    /**
     * Throws a NamedUserException on search failure.
     */
    public function throwNoMatchesException()
    {
        @\header('HTTP/1.1 404 Not Found');

        if (empty($this->query)) {
            throw new NamedUserException(WCF::getLanguage()->get('wcf.search.error.user.noMatches'));
        } else {
            throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
                'wcf.search.error.noMatches',
                ['query' => $this->query]
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function submit()
    {
        try {
            parent::submit();
        } catch (NamedUserException $e) {
            WCF::getTPL()->assign('errorMessage', $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        // get additional data
        $additionalData = [];
        foreach (SearchEngine::getInstance()->getAvailableObjectTypes() as $objectTypeName => $objectType) {
            if (($data = $objectType->getAdditionalData()) !== null) {
                $additionalData[$objectTypeName] = $data;
            }
        }

        // save result in database
        $this->searchData = [
            'packageID' => PACKAGE_ID,
            'query' => $this->query,
            'results' => $this->results,
            'additionalData' => $additionalData,
            'sortField' => $this->sortField,
            'sortOrder' => $this->sortOrder,
            'nameExactly' => $this->nameExactly,
            'subjectOnly' => $this->subjectOnly,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'username' => $this->username,
            'userID' => $this->userID,
            'selectedObjectTypes' => $this->selectedObjectTypes,
            'alterable' => !$this->userID ? 1 : 0,
        ];
        if ($this->modifySearchID) {
            $this->objectAction = new SearchAction([$this->modifySearchID], 'update', [
                'data' => [
                    'searchData' => \serialize($this->searchData),
                    'searchTime' => TIME_NOW,
                    'searchType' => 'messages',
                    'searchHash' => $this->searchHash,
                ],
            ]);
            $this->objectAction->executeAction();
        } else {
            $this->objectAction = new SearchAction([], 'create', [
                'data' => [
                    'userID' => WCF::getUser()->userID ?: null,
                    'searchData' => \serialize($this->searchData),
                    'searchTime' => TIME_NOW,
                    'searchType' => 'messages',
                    'searchHash' => $this->searchHash,
                ],
            ]);
            $resultValues = $this->objectAction->executeAction();
            $this->searchID = $resultValues['returnValues']->searchID;
        }
        // save keyword
        if (!empty($this->query)) {
            (new SearchKeywordAction([], 'registerSearch', [
                'data' => [
                    'keyword' => $this->query,
                ],
            ]))->executeAction();
        }
        $this->saved();

        // forward to result page
        HeaderUtil::redirect(LinkHandler::getInstance()->getLink(
            'SearchResult',
            ['id' => $this->searchID],
            'highlight=' . \urlencode($this->query)
        ));

        exit;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        // init form
        foreach (SearchEngine::getInstance()->getAvailableObjectTypes() as $objectType) {
            $objectType->show($this);
        }

        $searchPreselectObjectType = 'everywhere';
        if (\count($this->selectedObjectTypes) === 1) {
            $searchPreselectObjectType = \reset($this->selectedObjectTypes);
        }

        WCF::getTPL()->assign([
            'query' => $this->query,
            'subjectOnly' => $this->subjectOnly,
            'username' => $this->username,
            'nameExactly' => $this->nameExactly,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'sortField' => $this->sortField,
            'sortOrder' => $this->sortOrder,
            'selectedObjectTypes' => $this->selectedObjectTypes,
            'objectTypes' => SearchEngine::getInstance()->getAvailableObjectTypes(),
            'searchPreselectObjectType' => $searchPreselectObjectType,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        if (\count($_POST) == 1 && $this->submit) {
            if ($this->userID) {
                $this->useCaptcha = false;
            }
            $this->submit();
        }

        parent::show();
    }

    /**
     * Sets the conditions for a search in the table of the selected object types.
     */
    protected function getConditions()
    {
        if (empty($this->selectedObjectTypes)) {
            $this->selectedObjectTypes = \array_keys(SearchEngine::getInstance()->getAvailableObjectTypes());
        }

        // default conditions
        $userIDs = $this->getUserIDs();
        $conditionBuilderClassName = SearchEngine::getInstance()->getConditionBuilderClassName();
        $this->searchIndexCondition = new $conditionBuilderClassName(false);

        // user ids
        if (!empty($userIDs)) {
            $this->searchIndexCondition->add('userID IN (?)', [$userIDs]);
        }

        // dates
        $startDate = @\strtotime($this->startDate);
        $endDate = @\strtotime($this->endDate);
        if ($startDate && $endDate) {
            $this->searchIndexCondition->add('time BETWEEN ? AND ?', [$startDate, $endDate]);
        } elseif ($startDate) {
            $this->searchIndexCondition->add('time > ?', [$startDate]);
        } elseif ($endDate) {
            $this->searchIndexCondition->add('time < ?', [$endDate]);
        }

        // language
        if (!empty($this->query) && LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
            $this->searchIndexCondition->add(
                '(languageID IN (?) OR languageID = 0)',
                [WCF::getUser()->getLanguageIDs()]
            );
        }

        foreach ($this->selectedObjectTypes as $key => $objectTypeName) {
            $objectType = SearchEngine::getInstance()->getObjectType($objectTypeName);
            if ($objectType === null) {
                throw new SystemException('unknown search object type ' . $objectTypeName);
            }

            try {
                if (!$objectType->isAccessible()) {
                    throw new PermissionDeniedException();
                }

                // special conditions
                if (($conditionBuilder = $objectType->getConditions($this)) !== null) {
                    $this->additionalConditions[$objectTypeName] = $conditionBuilder;
                }
            } catch (PermissionDeniedException $e) {
                unset($this->selectedObjectTypes[$key]);
                continue;
            }
        }

        if (empty($this->selectedObjectTypes)) {
            $this->throwNoMatchesException();
        }
    }

    /**
     * Returns user ids.
     *
     * @return  int[]
     */
    public function getUserIDs()
    {
        return $this->userIDs;
    }

    /**
     * @inheritDoc
     */
    public function __run()
    {
        throw new IllegalLinkException();
    }
}
