<?php

namespace wcf\system\condition\type\request;

use wcf\data\page\PageNodeTree;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IGlobalConditionType;
use wcf\system\condition\type\IMigrateConditionType;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IGlobalConditionType<string>
 * @extends AbstractConditionType<string>
 */
final class NotOnPageRequestConditionType extends AbstractConditionType implements IGlobalConditionType, IMigrateConditionType
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'notOnPage';
    }

    #[\Override]
    public function getLabel(): string
    {
        return "wcf.condition.request.notOnPage";
    }

    #[\Override]
    public function getFormField(string $id): SingleSelectionFormField
    {
        // SelectFormField stores its value as a string,
        // so we need to convert it to an integer in the `matches` method.
        return SingleSelectionFormField::create($id)
            ->options((new PageNodeTree())->getNodeList(), true)
            ->required();
    }

    #[\Override]
    public function matches(): bool
    {
        return RequestHandler::getInstance()->getActivePageID() !== (int)$this->filter;
    }

    #[\Override]
    public function migrateConditionData(array &$conditionData): array
    {
        $reverseLogic = $conditionData['pageIDs_reverseLogic'] ?? false;
        $pageIDs = $conditionData['pageIDs'] ?? [];

        if (!$reverseLogic && \count($pageIDs) <= 1) {
            // `ActivePageRequestConditionType` should migrate the data.
            return [];
        }

        $conditions = [];
        if (!$reverseLogic) {
            // If reverse logic is not activated, we must add all unselected pages.
            // This allows us to turn an “or” condition into an “and” condition.
            $pageIDs = \array_diff($this->getPageIDs(), $pageIDs);
        }

        foreach ($pageIDs as $pageID) {
            $conditions[] = [
                'identifier' => $this->getIdentifier(),
                'value' => (string)$pageID,
            ];
        }

        unset($conditionData['pageIDs'], $conditionData['pageIDs_reverseLogic']);

        return $conditions;
    }

    /**
     * @return int[]
     */
    private function getPageIDs(): array
    {
        $sql = "SELECT pageID
                FROM   wcf1_page";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    #[\Override]
    public function canMigrateConditionData(string $objectType): bool
    {
        return $objectType === 'com.woltlab.wcf.page';
    }
}
