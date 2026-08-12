<?php

namespace wcf\acp\form;

use wcf\acp\page\LabelGroupListPage;
use wcf\data\label\group\LabelGroup;
use wcf\http\Helper;
use wcf\system\interaction\admin\LabelGroupInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the label group edit form.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LabelGroupEditForm extends LabelGroupAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.label.group.list';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(LabelGroup::class);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new LabelGroupInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(LabelGroupListPage::class)
            ),
        ]);
    }

    #[\Override]
    protected function setObjectTypeRelations(?array $data = null): void
    {
        if ($_POST === []) {
            // read database values
            $sql = "SELECT  objectTypeID, objectID
                    FROM    wcf1_label_group_to_object
                    WHERE   groupID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->formObject->groupID]);

            $data = [];
            while ($row = $statement->fetchArray()) {
                if (!isset($data[$row['objectTypeID']])) {
                    $data[$row['objectTypeID']] = [];
                }

                // prevent NULL values which confuse isset()
                $data[$row['objectTypeID']][] = $row['objectID'] ?: 0;
            }
        }

        parent::setObjectTypeRelations($data);
    }
}
