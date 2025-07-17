<?php

namespace wcf\system\condition\type\request;

use wcf\data\page\PageNodeTree;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IContextualConditionType;
use wcf\system\condition\type\IMigrateConditionType;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\request\RequestHandler;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IContextualConditionType<string[]>
 * @extends AbstractConditionType<string[]>
 */
final class ActivePageRequestConditionType extends AbstractConditionType implements IContextualConditionType, IMigrateConditionType
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'activePage';
    }

    #[\Override]
    public function getLabel(): string
    {
        return "wcf.condition.request.activePage";
    }

    #[\Override]
    public function getFormField(string $id): MultipleSelectionFormField
    {
        return MultipleSelectionFormField::create($id)
            ->options((new PageNodeTree())->getNodeList(), true)
            ->filterable()
            ->required();
    }

    #[\Override]
    public function matches(): bool
    {
        return \in_array(RequestHandler::getInstance()->getActivePageID(), $this->filter);
    }

    #[\Override]
    public function migrateConditionData(array &$conditionData): array
    {
        $reverseLogic = $conditionData['pageIDs_reverseLogic'] ?? false;
        $pageIDs = $conditionData['pageIDs'] ?? [];

        if ($reverseLogic) {
            // `NotOnPageRequestConditionType` should migrate the data.
            return [];
        }

        $conditions[] = [
            'identifier' => $this->getIdentifier(),
            'value' => \array_map('strval', $pageIDs),
        ];

        unset($conditionData['pageIDs'], $conditionData['pageIDs_reverseLogic']);

        return $conditions;
    }

    #[\Override]
    public function canMigrateConditionData(string $objectType): bool
    {
        return $objectType === 'com.woltlab.wcf.page';
    }
}
