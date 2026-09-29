<?php

namespace wcf\data\notice;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\cache\builder\NoticeCacheBuilder;
use wcf\system\condition\ConditionHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting notices.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Notice>
 */
final class NoticeBuilder extends DatabaseObjectBuilder
{
    private const LANGUAGE_CATEGORY = 'wcf.notice';

    private const LANGUAGE_ITEM_PATTERN = 'wcf.notice.notice.notice%d';

    /**
     * @var ?array<int, string>
     */
    private ?array $i18nValues = null;

    private ?int $showOrder = null;

    public function setNoticeName(string $noticeName): static
    {
        $this->properties['noticeName'] = $noticeName;

        return $this;
    }

    /**
     * Sets the text of the notice.
     *
     * Pass an array of language id to text mappings to store the text as a
     * language item instead of a plain value.
     *
     * @param string|array<int, string> $notice
     */
    public function setNotice(string|array $notice): static
    {
        if (\is_array($notice)) {
            $this->i18nValues = $notice;
            // The final value is the name of the language item which is only
            // known once the notice has been created.
            $this->properties['notice'] = '';
        } else {
            $this->i18nValues = [];
            $this->properties['notice'] = $notice;
        }

        return $this;
    }

    public function setNoticeUseHtml(bool $noticeUseHtml): static
    {
        $this->properties['noticeUseHtml'] = (int)$noticeUseHtml;

        return $this;
    }

    public function setCssClassName(string $cssClassName): static
    {
        $this->properties['cssClassName'] = $cssClassName;

        return $this;
    }

    /**
     * Sets the position of the notice in relation to the other notices.
     *
     * The other notices are shifted accordingly, a value of `0` appends the
     * notice at the end.
     */
    public function setShowOrder(int $showOrder): static
    {
        $this->showOrder = $showOrder;

        return $this;
    }

    public function setIsDisabled(bool $isDisabled): static
    {
        $this->properties['isDisabled'] = (int)$isDisabled;

        return $this;
    }

    public function setIsDismissible(bool $isDismissible): static
    {
        $this->properties['isDismissible'] = (int)$isDismissible;

        return $this;
    }

    /**
     * Sets the serialized object filters that the active user must match.
     *
     * A notice without any filters is visible to everyone, therefore an empty
     * list of filters is stored as `null`.
     */
    public function setConditions(?string $conditions): static
    {
        if ($conditions !== null && \json_decode($conditions, true) === []) {
            $conditions = null;
        }

        $this->properties['conditions'] = $conditions;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['noticeName'];
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        $this->saveI18nValues($object);

        if ($this->showOrder !== null) {
            self::applyShowOrder($object, $this->showOrder);
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        $this->saveI18nValues($object);

        if ($this->showOrder !== null && $this->showOrder !== $this->getObject()->showOrder) {
            self::applyShowOrder($object, $this->showOrder);
        }
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        ConditionHandler::getInstance()->deleteConditions('com.woltlab.wcf.condition.notice', $objectIDs);
    }

    /**
     * Writes the text of the notice into a language item or removes a
     * previously created language item if a plain value is used.
     */
    private function saveI18nValues(Notice $notice): void
    {
        if ($this->i18nValues === null) {
            return;
        }

        $languageItem = \sprintf(self::LANGUAGE_ITEM_PATTERN, $notice->noticeID);

        if ($this->i18nValues === []) {
            self::deleteLanguageItems([$languageItem]);

            return;
        }

        I18nHandler::getInstance()->save(
            $this->i18nValues,
            $languageItem,
            self::LANGUAGE_CATEGORY,
            \PACKAGE_ID
        );

        $sql = "UPDATE  wcf1_notice
                SET     notice = ?
                WHERE   noticeID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $languageItem,
            $notice->noticeID,
        ]);
    }

    /**
     * @param list<string> $languageItems
     */
    private static function deleteLanguageItems(array $languageItems): void
    {
        $sql = "SELECT  languageCategoryID
                FROM    wcf1_language_category
                WHERE   languageCategory = ?";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([self::LANGUAGE_CATEGORY]);
        $languageCategoryID = $statement->fetchSingleColumn();

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add('languageItem IN (?)', [$languageItems]);
        $conditions->add('packageID = ?', [\PACKAGE_ID]);
        $conditions->add('languageCategoryID = ?', [$languageCategoryID]);

        $sql = "DELETE FROM wcf1_language_item
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        LanguageFactory::getInstance()->deleteLanguageCache();
    }

    /**
     * Moves the notice to the given position, shifting the notices that follow
     * it. A value of `0` appends the notice at the end.
     */
    private static function applyShowOrder(Notice $notice, int $showOrder): void
    {
        $sql = "SELECT  MAX(showOrder)
                FROM    wcf1_notice";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $maxShowOrder = $statement->fetchSingleColumn();
        if ($maxShowOrder === null) {
            $maxShowOrder = 0;
        }

        if ($showOrder === 0 || $showOrder > $maxShowOrder) {
            $newShowOrder = $maxShowOrder + 1;
        } else {
            $sql = "UPDATE  wcf1_notice
                    SET     showOrder = showOrder + 1
                    WHERE   showOrder >= ?
                        AND noticeID <> ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $showOrder,
                $notice->noticeID,
            ]);

            $newShowOrder = $showOrder;
        }

        $sql = "UPDATE  wcf1_notice
                SET     showOrder = ?
                WHERE   noticeID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $newShowOrder,
            $notice->noticeID,
        ]);
    }

    public static function resetCache(): void
    {
        NoticeCacheBuilder::getInstance()->reset();
        ConditionCacheBuilder::getInstance()->reset([
            'definitionID' => ObjectTypeCache::getInstance()
                ->getDefinitionByName('com.woltlab.wcf.condition.notice')
                ->definitionID,
        ]);
    }
}
