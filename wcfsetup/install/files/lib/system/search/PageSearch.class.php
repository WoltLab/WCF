<?php

namespace wcf\system\search;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\content\SearchResultPageContent;
use wcf\data\page\content\SearchResultPageContentList;
use wcf\data\search\ISearchResultObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * An implementation of ISearchableObjectType for searching in cms pages.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class PageSearch extends AbstractSearchProvider
{
    /**
     * @var SearchResultPageContent[]
     */
    private $messageCache = [];

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs, ?array $additionalData = null): void
    {
        $list = new SearchResultPageContentList();
        $list->setObjectIDs($objectIDs);
        $list->readObjects();
        foreach ($list->getObjects() as $content) {
            $this->messageCache[$content->pageContentID] = $content;
        }
    }

    /**
     * @inheritDoc
     */
    public function getObject(int $objectID): ?ISearchResultObject
    {
        return $this->messageCache[$objectID] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return 'wcf1_page_content';
    }

    /**
     * @inheritDoc
     */
    public function getIDFieldName(): string
    {
        return $this->getTableName() . '.pageContentID';
    }

    /**
     * @inheritDoc
     */
    public function getSubjectFieldName(): string
    {
        return $this->getTableName() . '.title';
    }

    /**
     * @inheritDoc
     */
    public function getUsernameFieldName(): string
    {
        return "''";
    }

    /**
     * @inheritDoc
     */
    public function getTimeFieldName(): string
    {
        return 'wcf1_page_content.pageContentID';
    }

    /**
     * @inheritDoc
     */
    public function getConditionBuilder(array $parameters): ?PreparedStatementConditionBuilder
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add(
            'wcf1_page.pageType IN (?) AND wcf1_page.isDisabled = ?',
            [['text', 'html'], 0]
        );
        $this->initLanguageCondition($conditionBuilder);
        $this->initAclCondition($conditionBuilder);

        return $conditionBuilder;
    }

    /**
     * Restricts the results to a single version of each page, otherwise
     * multilingual pages would show up once per language. The version of the
     * active language is preferred, because that is the version the user is
     * being redirected to. Versions of disabled languages are excluded.
     */
    private function initLanguageCondition(PreparedStatementConditionBuilder $conditionBuilder): void
    {
        $conditionBuilder->add('wcf1_page_content.pageContentID = (
            SELECT      pageContentID
            FROM        wcf1_page_content
            WHERE       pageID = wcf1_page.pageID
                    AND (languageID IS NULL OR languageID IN (?))
            ORDER BY    IF(languageID = ?, 0, 1), pageContentID
            LIMIT       1
        )', [
            \array_keys(LanguageFactory::getInstance()->getLanguages()),
            WCF::getLanguage()->languageID,
        ]);
    }

    private function initAclCondition(PreparedStatementConditionBuilder $conditionBuilder): void
    {
        $objectTypeID = ObjectTypeCache::getInstance()
            ->getObjectTypeIDByName('com.woltlab.wcf.acl.simple', 'com.woltlab.wcf.page');
        $conditionBuilder->add('(
            wcf1_page_content.pageID NOT IN (
                SELECT  objectID
                FROM    wcf1_acl_simple_to_group
                WHERE   objectTypeID = ?
                UNION
                SELECT  objectID
                FROM    wcf1_acl_simple_to_user
                WHERE   objectTypeID = ?
            )
            OR
            wcf1_page_content.pageID IN (
                SELECT  objectID
                FROM    wcf1_acl_simple_to_group
                WHERE   objectTypeID = ?
                    AND groupID IN (?)
                UNION
                SELECT  objectID
                FROM    wcf1_acl_simple_to_user
                WHERE   objectTypeID = ?
                    AND userID = ?
            )
        )', [
            $objectTypeID,
            $objectTypeID,
            $objectTypeID,
            WCF::getUser()->getGroupIDs(),
            $objectTypeID,
            WCF::getUser()->userID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getJoins(): string
    {
        return '
            INNER JOIN  wcf1_page
            ON          wcf1_page.pageID = ' . $this->getTableName() . '.pageID';
    }

    /**
     * @inheritDoc
     */
    public function isAccessible(): bool
    {
        return !!SEARCH_ENABLE_PAGES;
    }
}
