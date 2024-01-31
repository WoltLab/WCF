<?php

namespace wcf\system\condition\page;

use wcf\data\condition\Condition;
use wcf\data\page\PageCache;
use wcf\data\page\PageNodeTree;
use wcf\system\condition\AbstractMultiSelectCondition;
use wcf\system\condition\AbstractSingleFieldCondition;
use wcf\system\condition\IContentCondition;
use wcf\system\exception\UserInputException;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Condition implementation for selecting multiple pages.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class MultiPageCondition extends AbstractMultiSelectCondition implements IContentCondition
{
    /**
     * @inheritDoc
     */
    protected $fieldName = 'pageIDs';

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.page.requestedPage';

    /**
     * is `true` if the logic should be reversed, thus all of the non-selected pages fulfill the
     * condition
     * @var bool
     */
    protected $reverseLogic = false;

    /**
     * @inheritDoc
     */
    public function getData()
    {
        if (!empty($this->fieldValue)) {
            return [
                $this->fieldName => $this->fieldValue,
                $this->fieldName . '_reverseLogic' => $this->reverseLogic,
            ];
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getFieldElement()
    {
        return WCF::getTPL()->fetch('shared_scrollablePageCheckboxList', 'wcf', [
            'pageCheckboxID' => $this->fieldName,
            'pageCheckboxListContainerID' => $this->fieldName . 'Container',
            'pageIDs' => $this->fieldValue,
            'pageNodeList' => (new PageNodeTree())->getNodeList(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        return WCF::getTPL()->fetch('shared_multiPageCondition', 'wcf', [
            'condition' => $this,
            'conditionHtml' => AbstractSingleFieldCondition::getHTML(),
            'fieldName' => $this->fieldName,
            'reverseLogic' => $this->reverseLogic,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getOptions()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST[$this->fieldName . '_reverseLogic'])) {
            $this->reverseLogic = (bool)$this->fieldName . '_reverseLogic';
        }
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->fieldValue = [];
        $this->reverseLogic = false;
    }

    /**
     * @inheritDoc
     */
    public function setData(Condition $condition)
    {
        parent::setData($condition);

        // backwards compatibility: if the reverse logic condition entry does not exist,
        // the logic is not reversed
        $this->reverseLogic = $condition->conditionData[$this->fieldName . '_reverseLogic'] ?? false;
    }

    /**
     * @inheritDoc
     */
    public function showContent(Condition $condition)
    {
        $pageID = RequestHandler::getInstance()->getActivePageID();
        if ($pageID !== null) {
            $pageIDs = $condition->{$this->fieldName};

            if ($condition->pageIDs && \is_array($pageIDs)) {
                $matchingPageID = \in_array($pageID, $pageIDs);

                if ($condition->{$this->fieldName . '_reverseLogic'}) {
                    return !$matchingPageID;
                }

                return $matchingPageID;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        foreach ($this->fieldValue as $value) {
            if (PageCache::getInstance()->getPage($value) === null) {
                $this->errorMessage = 'wcf.global.form.error.noValidSelection';

                throw new UserInputException($this->fieldName, 'noValidSelection');
            }
        }
    }
}
