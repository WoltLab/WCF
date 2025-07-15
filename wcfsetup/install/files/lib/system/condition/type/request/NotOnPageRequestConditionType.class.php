<?php

namespace wcf\system\condition\type\request;

use wcf\data\page\PageNodeTree;
use wcf\system\condition\type\AbstractConditionType;
use wcf\system\condition\type\IGlobalConditionType;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\request\RequestHandler;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @implements IGlobalConditionType<string>
 * @extends AbstractConditionType<string>
 */
final class NotOnPageRequestConditionType extends AbstractConditionType implements IGlobalConditionType
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
}
